<?php

class Str{
    
    // https://gist.github.com/keithmorris/4155220#file-remove-words-php-L5
	// https://github.com/rap2hpoutre/remove-stop-words 
    static $stopwords=[
        'es' => ['a','acá','ahí','ajena/o/s','al','algo','algún/a/o/s','allá/í','ambos','ante','antes','aquel','aquella/o/s','aquí','arriba','así','atrás','aun','aunque','bajo','bastante','bien','cabe','cada','casi','cierto/a/s','como','con','conmigo','conseguimos','conseguir','consigo','consigue','consiguen','consigues','contigo','contra','cual','cuales','cualquier/a/s','cuan','cuando','cuanto/a/s','de','dejar','del','demás','demasiada/o/s','dentro','desde','donde','dos','el','él','ella/o/s','empleáis','emplean','emplear','empleas','empleo','en','encima','entonces','entre','era/s','eramos','eran','eres','es','esa/e/o/s','esta/s','estaba','estado','estáis','estamos','están','estar','este/o/s','estoy','etc','fin','fue','fueron','fui','fuimos','gue no','ha','hace/s','hacéis','hacemos','hacen','hacer','hacia','hago','hasta','incluso','intenta/s','intentáis','intentamos','intentan','intentar','intento','ir','jamás','junto/s','la/o/s','largo','más','me','menos','mi/s','mía/s','mientras','mío/s','misma/o/s','modo',
                 'mucha/s','muchísima/o/s','mucho/s','muy','nada','ni','ningún/a/o/s','no','nos','nosotras/os','nuestra/o/s','nunca','os','otra/o/s','para','parecer','pero','poca/o/s','podéis','podemos','poder','podría/s','podríais','podríamos','podrían','por','por qué','porque','primero','puede/n','puedo','pues','que','qué','querer','quién/es','quienesquiera','quienquiera','quizá/s','sabe/s/n','sabéis','sabemos','saber','se','según','ser','si','sí','siempre','siendo','sin','sino','so','sobre','sois','solamente','solo','sólo','somos','soy','sr','sra','sres','sta','su/s','suya/o/s','tal/es','también','tampoco','tan','tanta/o/s','te','tenéis','tenemos','tener','tengo','ti','tiempo','tiene','tienen','toda/o/s','tomar','trabaja/o','trabajáis','trabajamos','trabajan','trabajar','trabajas','tras','tú','tu','tus','tuya/o/s','último','ultimo','un/a/o/s','usa/s','usáis','usamos','usan','usar','uso','usted/es','va/n','vais','valor','vamos','varias/os','vaya','verdadera','vosotras/os','voy','vuestra/o/s','y','ya','yo']
       ,'en' => ['a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing',
                 'contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself',
                 'he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none',
                 'nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime',
                 'sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was',
                 'wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero']
    ];
    /* 
    public static function  removeStopWords($text,$lang='es'){
        //   return preg_replace('/\b('.implode('|',self::$stopwords[$lang]).')\b/','',$text);
        //   $input = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $input));
        $stop_words = self::$stopwords['es'];
        foreach ($stop_words as &$word) {
            $word = '/\b' . preg_quote($word, '/') . '\b/iu';
        }
        return preg_replace($stop_words, '', $text); 
    }
    */ 
    public static function removeStopWords($text, $lang = 'es') {
        $stop_words = self::$stopwords[$lang] ?? [];
        $words = explode(' ', $text);
        $filtered_words = array_filter($words, function($word) use ($stop_words) {
            return !in_array(mb_strtolower($word), $stop_words);
        });
        return implode(' ', $filtered_words);
    }

    public static function padr($s,$len,$char=' '){
        while (mb_strlen($s)<$len)  $s = $s.$char;
        return $s;
    }    

    public static function padl($s,$len,$char=' '){
        while (mb_strlen($s)<$len)  $s = $char.$s;
        return $s;
    } 

    public static function replicate($len,$char=' '){
       return self::padr($char,$len,$char);
    } 
  
    /**

    No hay en español rastros de tla, tle, … o mra, mre, … .

    Al parecer *casi todas las lenguas forman palabras de acuerdo con una plantilla o modelo 
    predefinido, de la forma un “ataque-núcleo-coda” [al parecer es más fácil organizar sistemas
    de delimitación de palabras y morfemas si todo está organizado respecto a un patrón de ese
    tipo, y esa parece ser la razón por la cual las lenguas usan ese esquema].

    Además en español y muchas otras lenguas existe una escala de sonoridad, así los fonemas se 
    agrupan subiendo la sonoridad hasta el núcleo silábico (que es lo más sonoro) y luego desciende
    la sonoridad en la coda. Así se logra que el núcleo sea la cúspide de sonoridad. Pues bien el 
    español tiene el siguiente patrón máximo:

    (K)-(L)-V-(Kc)-(s)

    donde K es una consonante cualquiera, L es una consonante líquida, V sólo puede ser una
    vocálico (monoptongo, diptongo o tripotongo) y Kc es alguna consonante coronal. Los términos 
    entre paréntesis pueden aparecer o no (en español es obligatorio que una sílaba contenga alguna 
    vocal, aunque no en otras lenguas). Y luego hay restricciones:

    Si L aparece entonces K tiene que ser necesariamente obstruyente: esto excluye mra, mre…, nla, nle, …, etc.
    Si L aparece entonces la consonante K no puede ser coronal: esto excluye dla, dle, dli, … y 
    también sla, sle, … Si L no aparece entonces K puede ser cualquiera (es no es una restricción).
    Las consonantes coronales Kc = {-d, -s, -z -n, -l, -r} son los únicos finales de sílaba permitidos 
    a final de palabra, y son los más frecuentes también en interior de palabra (en español en interior 
    de palabra se permiten otras consonantes de final de sílaba pero son todo cultismos: acto, apto … ).
    Se pueden dar otras restricciones adicionales (por ejemplo si la coda silábica es compuesta, necesariamente
    acaba en -ns: trans-por-te, ins-tau-rar). Obviamente el cada lengua tiene sus propias restricciones, el 
    latín tenía sílabas más complicadas que el español y también el inglés tiene sílabas más complicadas. Pero
    en cambio el japonés tiene sílabas en general más simples.

    */

    public static function password($length=0, $strength=0) {
        $password = '';
        $vowels  = 'aeiou';
        $vowels .= 'aei';
        $vowels .= $vowels;
        $consonants  = 'cfklbdghjmnpqrstvwxyz';
        $consonants .= 'klbrcfdgjmnvpstz';
        $consonants .= 'lbdjmnpcfrst';
        $consonants .= 'djmnclbps';
        $consonants .= 'sdlnmb';
        $consonants .= $consonants;
        
        $maychars = 'SDLNMBDJCPSDGHQRTVWXZAEUY';
        $numchars = '234567890';
        $specialchars = '@#$%,';  // '!@#$%^&*?_~-,';  // 
        
        $specialcharfound = false;
        $maycharfound = false;
        $numcharfound = false;
        /** 
        if ((password.length >0) && (password.length <=5)) passwordStrength=1;                // 1 >0 && <=5
        if (password.length >= options.minLength) passwordStrength++;                         // 2 >=8
        if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/)) ) passwordStrength++;
        if (password.match(/\d+/)) passwordStrength++;
        if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))        passwordStrength++;
        if (password.length > 12) passwordStrength++;
        **/
        if ($strength >= 1) $consonants .= 'SDLNMBDJMNCLBPSBDGHJLMNPQRSTVWXZ';
        if ($strength >= 2) $vowels     .= 'AEAEUAEUY';
        if ($strength >= 3) $consonants .= $numchars.$numchars;
        if ($strength >= 4) $consonants .= $specialchars.$specialchars;
        $consonants .= str_shuffle($consonants);
        $vowels .= str_shuffle($vowels);
        srand((double)microtime()*1000000);
        
        if(!$length) $length = 4 + random_int(0, 5);// $length = 4 + rand() % 6;
        /*
        $specialcharpos = 1 + rand() % ($length - 1);
        $maycharpos = 1 + rand() % ($length - 1);
        $numcharpos = 1 + rand() % ($length - 1);
        */
        $specialcharpos = random_int(1, $length - 1);
        $maycharpos     = random_int(1, $length - 1);
        $numcharpos     = random_int(1, $length - 1);
              
        $alt = time() % 2;
        $n_3_c = 0;
        $n_3_v = 0;
        $nn = 0;
        $char = false;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1)  {
                $consonants = str_shuffle($consonants);
                $char = $consonants[random_int(0, strlen($consonants) - 1)]; // $consonants[(rand() % strlen($consonants))];
                if($n_3_c<1) {
                    $alt = 0;
                    $n_3_c++ ;
                } else if($n_3_c<2) {
                    $_3_c = random_int(0, 5);  //rand() % 6;
                    if($_3_c<4) $alt = 0;
                    $n_3_c++ ;
                } else {
                    $alt=0;
                }
            } else {
                $char =$vowels[random_int(0, strlen($vowels) - 1)]; // $vowels[(rand() % strlen($vowels))];
                if($n_3_v<2) {
                    $alt = 1;
                    $n_3_v++ ;
                } else if($n_3_v<3) {
                    $_3_v = random_int(0, 13); //rand() % 14;
                    if($_3_v<12) $alt = 1;
                    $n_3_v++ ;
               } else {
                   $alt=1;
               }
            }
            $nn++;
            if($nn>2) {
                $i_n = random_int(0, 5); //rand() % 6;
                if($i_n==3) {
                     $char = random_int(0, 98);  //rand() % 99;
                     $nn=true;
                     $i++;
                     $i++;
                }
                $nn=-1;
            }

            if($char) {
               
                if($strength >= 2 && !$maycharfound && strpos($maychars, $char) === false && $maycharpos==$i) {
                    //$password .= $maychars[(rand() % strlen($maychars))];
                    $password .= $maychars[random_int(0, strlen($maychars) - 1)];
                    $maycharfound=true;  // found may char
                }
               
                if($strength >= 3 && !$numcharfound && strpos($numchars, $char) === false && $numcharpos==$i){
                    //$password .= $numchars[(rand() % strlen($numchars))];
                    $password .= $numchars[random_int(0, strlen($numchars) - 1)];
                    $numcharfound=true;  // found number char
                }
               
                if($strength >= 4 && !$specialcharfound && strpos($specialchars, $char) === false && $specialcharpos==$i){
                    //$password .= $specialchars[(rand() % strlen($specialchars))];
                    $password .= $specialchars[random_int(0, strlen($specialchars) - 1)];
                    $specialcharfound=true;  // found specia char
                }
               
                $password .= $char;
            }
            
            $char = false;

        }
        return $password;
    }  
    
    //unset($_SESSION['popupx']);  
    //+ Jonas Raoni Soares Silva
    //@ https://jsfromhell.com
    public static function truncate($s, $l=300, $e = '...', $isHTML = false){
        if($l<1) return $s;  
        $i = 0;
        $tags = array();
        if($isHTML){
            preg_match_all('/<[^>]+>([^<]*)/', $s, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            foreach($m as $o){
            if($o[0][1] - $i >= $l)    break;
            $t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
            if      ($t[0] != '/')                $tags[] = $t;
            else if (end($tags) == substr($t, 1))  array_pop($tags);
            $i += $o[1][1] - $o[0][1];
            }
        }
        return substr($s, 0, $l = min(strlen($s),  $l + $i)) 
             . (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '') 
             . (strlen($s) > $l ? $e : '');
    }
    
    // función by lorang at e-connect dot lu (http://es.php.net/manual/es/function.wordwrap.php)
    //lorang at e-connect dot lu
    //10-May-2002 11:25
    //Limits a text to a certain number of characters, but should always keep the whole words
    public static function limit_text($text,$maxchar){
        $split=explode(" ",$text);
        $newtext = '';
        $i=0;
        while(TRUE){
            $len = (strlen($newtext ?? '') + strlen($split[$i] ?? ''));
            if($len>$maxchar){
                break;
            }else{
                $newtext=$newtext." ".$split[$i];
                $i++;
            }
        }
        if (strlen($text)>$maxchar)$newtext=$newtext.' ...';
        return $newtext;
    }

    public static function _is_valid($string, $min_length, $max_length, $regex){ 
        $str = trim($string);   // Check if the string is empty
        if(empty($str))  {return(false);}
        if(!preg_match("/^$regex$/i", $string)) {return(false); } // Does string entirely consist of characters of $type?
        $strlen = strlen($string); // Check for the optional length specifiers
        if(($min_length != 0 && $strlen < $min_length) || ($max_length != 0 && $strlen > $max_length)) {return(false); }
        return(true);
    }

    public static function valid_email($address){
        //return (preg_match("/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/", $address)) ? true : false ;
        return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function is_clean_text($string, $min_length = 0, $max_length = 0){
        $ret = self::_is_valid($string, $min_length, $max_length, "[a-zA-Z0-9[:space:]ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþ`´'()¿?ºª\.\-,;]+");
        return($ret);
    }

    public static function is_ascii_text($string, $min_length = 0, $max_length = 0){
        $ret = self::_is_valid($string, $min_length, $max_length, "[a-zA-Z0-9]+");
        return($ret);
    }

    public static function is_friendly_name($string, $min_length = 0, $max_length = 0){
        $ret = self::_is_valid($string, $min_length, $max_length, "[_a-zA-Z0-9\-]+");
        return($ret);
    }

    public static function is_valid_username($string, $min_length = 0, $max_length = 0){
        // $ret = is_ascii_text($string, 1 , 15);
        $ret = self::_is_valid($string, 4, 30, "[a-zA-Z]{1}[a-zA-Z_\-0-9\.]+");
        return($ret);
    }

    public static function is_valid_rfid($string){
        $ret = self::_is_valid($string, 10, 10, "[0-9]+");
        return($ret);
    }

    // Devuelve false en caso de que el parametros recibido contenga algún
    // caracter NO comprendido entre 0 y 9, de lo contrario devuelve true.
    public static function is_numeric($n) {
        /*
        $p=$n;
        $l=strlen($p);
        $in = true;
        for ($t=0; $t<$l; $t++) {
        $c=substr($p,$t,1);
        //if($c==',') continue;
        //if($c=='€') continue;
        if ($c<'0' || $c>'9') { $in=false; }
        }
        return $in;
        */
        return preg_match('/^(0|[1-9][0-9]*)$/', (string)$n) === 1;
    }

    /*
    public static function is_float($n) {
        $p=$n;
        $l=strlen($p);
        $in = true;
        for ($t=0; $t<$l; $t++) {
            $c=substr($p,$t,1);
            if($c==',') continue;
            if    ($c<'0' || $c>'9') { $in=false; }
        }
        return $in;
    }
    */

    // https://stackoverflow.com/questions/10752862/password-strength-check-in-php
    public static function checkPassword($pwd, &$errors) {
        $errors_init = $errors;
        if (strlen($pwd) < 8)              { $errors[] = "Password too short!";   }
        if (strlen($pwd) > 20 )            { $errors[] = "Password too long!";    }
        if (!preg_match("#[0-9]+#", $pwd)) { $errors[] = "Password must include at least one number!"; }
        if (!preg_match("#[a-z]+#", $pwd)) { $errors[] = "Password must include at least one letter!"; }    
        if (!preg_match("#[A-Z]+#", $pwd)) { $errors[] = "Password must include at least one CAPS!";   }
        if (!preg_match("#\W+#"   , $pwd)) { $errors[] = "Password must include at least one symbol!"; }
        return ($errors == $errors_init);
    }    

    public static function valid_password($password) {

        if ($special_chars && !preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
            Messages::error('La contraseña no tiene al menos un carácter especial');
            return false;
        }

        if (!preg_match("/[A-Z]/", $password)) {
            Messages::error('La contraseña no tiene al menos una letra mayúscula');
            return false;
        }

        if (!preg_match("/[a-z]/", $password)) {
            Messages::error('La contraseña no tiene al menos una letra minúscula');
            return false;
        }

        if (!preg_match("/[0-9]/", $password)) {
            Messages::error('La contraseña no tiene al menos un dígito');
            return false;
        }

        //if (!preg_match("/[0-9]/", $password) && !preg_match("/[^A-Za-z0-9]/", $password)) {
        //  Messages::error('La contraseña no tiene al menos un dígito o un carácter no alfanumérico');
        //  return false;
        //}

        if (strlen($password) < 8) {
            Messages::error('La contraseña no tiene la longitud mínima de 8 caracteres');
            return false;
        }

        // Si llega hasta aquí, la contraseña cumple con todos los requisitos
        return true;
    }
    
        
    public static function _valid_password($password,$min_len=8,$special_chars=false){
         $errors = [];

         if ($special_chars && !preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
            $errors[] = 'La contraseña no tiene al menos un carácter especial';
        }
        
        if ($special_chars && !preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
           $errors[] ='La contraseña no tiene al menos un carácter especial';
        }

        if (!preg_match("/[A-Z]/", $password)) {
           $errors[] ='La contraseña no tiene al menos una letra mayúscula';
        }

        if (!preg_match("/[a-z]/", $password)) {
            $errors[] ='La contraseña no tiene al menos una letra minúscula';
        }

        if (!preg_match("/[0-9]/", $password)) {
            $errors[] ='La contraseña no tiene al menos un dígito';
        }

        //if (!preg_match("/[0-9]/", $password) && !preg_match("/[^A-Za-z0-9]/", $password)) {
        //  Messages::error('La contraseña no tiene al menos un dígito o un carácter no alfanumérico');
        //  return false;
        //}

        if (strlen($password) < $min_len) {
            $errors[] ='La contraseña no tiene la longitud mínima de '.$min_len.' caracteres';
        }

 
        return ['error' => count($errors)>0, 'errors' => $errors];

    }

    public static function sanitizeName($s,$is_filename=false){

        // Handle null input
        if ($s === null || $s === '') {
            return $is_filename ? '.' : '';
        }

        if($is_filename){
            $ext =  self::get_file_extension($s);
            $s =  self::get_file_name($s);
            
            // Handle case where get_file_name returns null/empty
            if ($s === null || $s === '') {
                return '.' . $ext;
            }
        }

        $point = $is_filename ? '\.' : '' ;
        $space = $is_filename ? '_'  : '-';
        //Rememplazamos caracteres especiales latinos
        $find = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ç', 'Ç', '€');
        $repl = array('a', 'e', 'i', 'o', 'u', 'n', 'n', 'a', 'e', 'i', 'o', 'u', 'c', 'c', 'e');
        $s = str_replace ($find, $repl, $s);
        // Tranformamos todo a minusculas
        //FIX $s = mb_strtolower($s);
        $s = strtolower($s);
        // Añaadimos los guiones
        $find = array(' ', '&', '\r\n', '\n', '+');
        $s = str_replace ($find, $space, $s);
        ///###########OK $s = str_replace ($find, '-', $s);
        // Eliminamos y Reemplazamos demás caracteres especiales
        //$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
        //if($is_filename){
            $find = array( '/[^a-z_\-0-9'.$point.']/',/* '/[\-]+/',*/  '/<[^>]*>/');
            //###########OK $repl = array('', '-', '');
            $repl = array( '',/*$space,*/ '');
            $s = preg_replace ($find, $repl, $s);
        //}
        return $is_filename?$s.'.'.$ext:$s;
    }

    public static function valid_nif_cif_nie($cif) {
        //returns: 1 = NIF ok, 2 = CIF ok, 3 = NIE ok, -1 = NIF bad, -2 = CIF bad, -3 = NIE bad, 0 = ??? bad
        //función creada por David Vidal Serra, Copyleft 2005
        $return = 0;
        $cif=strtoupper($cif);
        if (preg_match('/(^[A-Z]{1}[0-9]{7}[A-Z]{1}$)/',$cif)) { return  1;} 
        if (!preg_match('/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/',$cif)) {return 0;}
        for ($i=0;$i<9;$i++) {$num[$i]=substr($cif,$i,1);}
        $suma=$num[2]+$num[4]+$num[6];
        for ($i=1;$i<8;$i+=2) {$suma+=substr((2*$num[$i]),0,1)+substr((2*$num[$i]),1,1);}
        $n=10-substr($suma,strlen($suma)-1,1);
        if      (preg_match('/^[ABCDEFGHNPQS]{1}/',$cif))           { if ($num[8]==chr(64+$n) || $num[8]==substr($n,strlen($n)-1,1)){$return = 2;} else {$return =  -2;}                                                                           }
        else if (preg_match('/^[KLM]{1}/',$cif))                    { if ($num[8]==chr(64+$n)) {$return =  2;} else {$return =  -2;}                                                                                                               }
        else if (preg_match('/^[TX]{1}/',$cif))                     { if ($num[8]==substr('TRWAGMYFPDXBNJZSQVHLCKE',substr(mb_ereg_replace('X','0',$cif),0,8)%23,1) || preg_match('/^[T]{1}[A-Z0-9]{8}$/',$cif)) {$return =  3;} else {$return =  -3;}}
        else if (preg_match('/(^[0-9]{8}[A-Z]{1}$)/',$cif))         { if ($num[8]==substr('TRWAGMYFPDXBNJZSQVHLCKE',substr($cif,0,8)%23,1)) {$return =  1;} else {$return =  -1;}                                                                  }
        //      else if (preg_match('/(^[A-Z]{1}[0-9]{7}[A-Z]{1}$)/',$cif)) { $return =  1;}                                                                                                                                                                }
        return $return;
    } 


    public static function get_file_extension($file_name) {
        //return pathinfo($file_name, PATHINFO_EXTENSION);
        return $file_name ? pathinfo($file_name, PATHINFO_EXTENSION) : '';
    }

    public static function get_file_name($file_name) {
        preg_match("/(.+)\.(.+)/", basename(stripslashes($file_name)), $regs);
        return $regs[1];
    }

    public static function get_file_name_and_extension($file_name) {
        preg_match("/(.+)\.(.+)/", basename(stripslashes($file_name)), $regs);
        return $regs[1].'.'.$regs[2];
    }

    public static function pretty_file_name($file_name) {
        return ucfirst(str_replace ('_', ' ', get_file_name($file_name) ) );
    }

    public static function word2regexp($s){
      $s = preg_replace('/[aàáâãåäæ]/iu', '(a|à|á|â|ã|å|ä|æ)', $s);
      $s = preg_replace('/[eèéêë]/iu'   , '(e|è|é|ê|ë)'      , $s);
      $s = preg_replace('/[iìíîï]/iu'   , '(i|ì|í|î|ï)'      , $s);
      $s = preg_replace('/[oòóôõöø]/iu' , '(o|ò|ó|ô|õ|ö|ø)'  , $s);
      $s = preg_replace('/[uùúûü]/iu'   , '(u|ù|ú|û|ü)'      , $s);
      $s = preg_replace('/[ñ]/iu'       , '(ñ)'              , $s);
      return $s;
    } 

    //http://stackoverflow.com/questions/7798829/php-regular-expression-to-match-keyword-outside-html-tag-a
    public static function colorizeSearchText($searchtext,$text) {
        if(!$searchtext) return $text;
        //$colores=array('#FFFF00','#00F4F4','#FF00FF','#00FF00','#0080FF');
        $clases=array('match1','match2','match3','match4','match5');
        $n=0;
        $scan=$searchtext;
        $ascan = explode( " ", $scan );
        for ( $i=0; $i <= count($ascan)-1; $i++ ) {
        if(strlen($ascan[$i])>2){
            $scani=self::word2regexp($ascan[$i]);
            $text = preg_replace('~'.$scani.'(?!(?>[^<]*(?:<(?!/?a\b)[^<]*)*)</a>)~i', '<span class="'.$clases[$n].'">${0}</span>', $text);
            $n++;
            if($n>4) $n=0;
        }
        }
        return $text;
    }

    public static function formatBytes($size) {
        $mb = 1024*1024;
        $gb = $mb*1024;
        if     ( $size > $gb )   { $mysize = sprintf ("%01.2f",$size/$gb) . "G";}
        elseif ( $size > $mb )   { $mysize = sprintf ("%01.2f",$size/$mb) . "M";}
        elseif ( $size >= 1024 ) { $mysize = sprintf ("%01.2f",$size/1024) . "K"; }
        else                     { $mysize = $size . "b";}
        return $mysize;
    }

    public static function formatBytesColorized($size) {
        $mb = 1024*1024;
        $gb = $mb*1024;
        if     ( $size > $gb )   { $mysize = '<span style="color:var(--red);">'.sprintf ("%01.2f",$size/$gb) . "<b>G</b></span>";}
        elseif ( $size > $mb )   { $mysize = '<span style="color:var(--orange);">'.sprintf ("%01.2f",$size/$mb) . "<b>M</b></span>";}
        elseif ( $size >= 1024 ) { $mysize = '<span style="color:silver;">'.sprintf ("%01.2f",$size/1024) . "<b>K</b></span>"; }
        else                     { $mysize = '<span style="color:var(--gray);">'.$size . "<b>b</b></span>";}
        return $mysize;
    }


    public static function escape($str) {                               
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
        return str_replace($search, $replace, $str??'');
    }                                                                       //ADD 20140423

	
    public static function unescape($str) {                               
        $search = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");  //,"\n",);
        $replace = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");  //,"<br />");
        return str_replace($search, $replace, $str??'');
    }

    /***
    function test0($string){
        $s  = $string;
        $p1 = strpos($s,'(');
        $p2 = strpos($s,')');
        $p1 = $p1>-1 ? $p1+1 : 0;
        $p2 = $p2 ? $p2 : strlen($s);
        return substr($s, $p1, $p2-$p1)."\n";
    }

    function test1($string){
        preg_match( '!\(([^\)]+)\)!', $string, $match );
        return  $match[0]."\n";
    }

    function test2($string){
        preg_match('#\((.*?)\)#', $string, $match);
        return  $match[0]."\n";
    }

    function test3($string){
        return explode(')', (explode('(', $string)[1]))[0]."\n";
    }

    echo'<pre>';
    echo test('(Holaabc)defgh');  //
    echo test('H(olaabc)defgh');  //+
    echo test('Ho(laabc)defgh');  //+
    echo test('Holaabcdefgh)');   //+  
    echo test('Holaabcde(fgh)');  //+
    echo test('Ho(laabcde(fg)h');  //+   
    echo test('Holaabcdef)gh');   //+
    cho test('Holaabcde)fgh');   //+
    echo'</pre>';
    **/	



    public static function html2text($text) {
        return html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
    }

    public static function end_with($str,$separator = DIRECTORY_SEPARATOR	){ 
        return rtrim($str,$separator).$separator;	
    }

    // Versionado Semántico Estándar (MAJOR.MINOR.PATCH)
    public static function increment_version($v) {
        $p = array_map('intval', explode('.', $v));
        
        // Asegurar que siempre haya 3 posiciones
        while (count($p) < 3) {
            $p[] = 0;
        }
        
        // Incrementar el patch (última posición)
        $p[2]++;
        
        // NO hay límites - las versiones pueden crecer infinitamente
        // Simplemente devolver el resultado
        return implode('.', array_slice($p, 0, 3));
    }

        
    // Convierte emojis en su representación HTML numérica, evitando procesar el HTML existente.
    // Basado en: https://stackoverflow.com/questions/30757193/php-convert-emoji-to-html-entities
    // Uso: $safe_string = Utils::emoji_to_html($input_string);
    //      if (Utils::has_emojis($input_string)) { ... }
    public static function emoji_to_html($string) {
        // Divide la cadena en texto y etiquetas HTML para evitar procesar el HTML.
        $parts = preg_split('/(<[^>]+>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';

        // Regex más preciso que solo apunta a caracteres que son emojis visuales por defecto.
        $emoji_regex = '/\p{Emoji_Presentation}|\p{Extended_Pictographic}/u';

        foreach ($parts as $i => $part) {
            // Las partes pares son texto, las impares son etiquetas.
            if ($i % 2 == 0) {
                // Es contenido de texto, convertir emojis aquí.
                $result .= preg_replace_callback($emoji_regex, function ($match) {
                    return '&#' . mb_ord($match[0]) . ';';
                }, $part);
            } else {
                // Es una etiqueta HTML, añadirla tal cual.
                $result .= $part;
            }
        }
        return $result;
    }

    public static function has_emojis($string) {
        // Divide la cadena para no buscar emojis dentro de las etiquetas HTML.
        $parts = preg_split('/(<[^>]+>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $emoji_regex = '/\p{Emoji_Presentation}|\p{Extended_Pictographic}/u';

        foreach ($parts as $i => $part) {
            if ($i % 2 == 0) {
                // Es contenido de texto, buscar emojis aquí.
                if (preg_match($emoji_regex, $part)) {
                    return true;
                }
            }
        }
        return false;
    }


/**
 * 
 * 
 * El problema es que la base de datos no soporta emojis porque necesita el charset utf8mb4 en lugar de utf8. El charset utf8 de MySQL solo soporta caracteres de hasta 3 bytes, pero los emojis necesitan 4 bytes.

Hay varias opciones para solucionarlo:

Opción 1: Cambiar el charset de la base de datos y tablas a utf8mb4

-- Cambiar la base de datos
ALTER DATABASE nombre_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Cambiar cada tabla que necesite emojis
ALTER TABLE NOT_NEWS CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE POST_COMMENTS CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- etc.



Opción 2: Configurar la conexión en el framework
Necesitas asegurarte de que la conexión PDO use utf8mb4. Revisa tu archivo de configuración o el trait de conexión MySQL:

connection.mysql.trait.php
Busca donde se establece el charset y cámbialo:

connection.mysql.trait.php+8-0
Opción 3: Convertir emojis a entidades HTML (workaround sin cambiar BD)
Si no puedes cambiar la base de datos, puedes codificar los emojis antes de guardar y decodificarlos al mostrar:

str.class.php+17-0
Luego usarías:


// Al guardar
$text = Str::encodeEmojis($text);

// Al mostrar
$text = Str::decodeEmojis($text);

Mi recomendación: La Opción 1 + Opción 2 es la correcta. Cambiar a utf8mb4 es el estándar moderno y evita problemas futuros. La Opción 3 es solo un workaround temporal.


 * 
 * 
 * 
 * 
 */

    /**
     * Convierte emojis a entidades HTML para guardar en BD utf8
     */
    public static function encodeEmojis($text) {
        return preg_replace_callback('/[\x{10000}-\x{10FFFF}]/u', function($match) {
            $char = $match[0];
            $codepoint = mb_ord($char, 'UTF-8');
            return '&#x' . dechex($codepoint) . ';';
        }, $text);
    }

    /**
     * Decodifica entidades HTML de emojis
     */
    public static function decodeEmojis($text) {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

}