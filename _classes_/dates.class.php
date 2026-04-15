<?php
	
// https://victorroblesweb.es/2016/11/12/cuantos-dias-fechas-php/

	
class HaceCuanto{
    
    
    function CambiaLetra($letra, $buscar, $cambiar) {
        return str_replace( array(mb_strtolower($buscar,'UTF-8'),mb_strtoupper($buscar,'UTF-8')),
                            array(mb_strtolower($cambiar,'UTF-8'),mb_strtoupper($cambiar,'UTF-8')),$letra);
    }

    public static function plural($s,$n=1) {
        $len = mb_strlen($s,'UTF-8'); // Obtener la longitud de la palabra
        $u = ctype_upper($s);
        $l = $s[strlen($s)-1] ;    
        $p = $s[strlen($s)-2] ;    
      //$l = mb_substr($s,$len-1,1,'UTF-8');	// Extraer el último carácter
      //$p = mb_substr($s,$len-2,1,'UTF-8');	// Extraer el penúltimo carácter
        $v = strpos('_UAEIOUaeiou',$l);   
     // $v = (strpos('aeiouáéíóú',mb_strtolower($l,'UTF-8'))===true);  // $l es vocal
        $pt = strpos('_áéíóúÁÉÍÓÚ',$p);    // penultima $vocal con tilde
     // $pt = (strpos('áéíóú',mb_strtolower($p,'UTF-8'))===true);    // penultima $vocal con tilde
        $m  = strpos('_abcdefghijklmnñopqrstuvwxyzáéíóú',$l);  // $letra es minúscula
        $sx = strpos('_sxSX',$l);
        if (($n==1)||($sx && !$pt))  return $s;
        else 
        return $s.($n==1?'':  ($v&&$u?'S':($v?'s':($u?'ES':'es')))  );  
    }
    
    private static function p1($n,$w){
	    return $n . ' ' . self::plural($w,$n);
    }
    
    private static function p2($n,$w){
	    return $n<1 ? '' : ' y '.self::p1($n,$w); 
    }
    
    public static function imprimirTiempo($fecha_y_hora){
        $f = new DateTime($fecha_y_hora);
        $n = new DateTime(date("Y-m-d H:i:s"));
        $d = $f->diff($n);
        echo ($n < $f ? 'Faltan ':'Hace ') 
        .( $d->y > 0 ? (self::p1($d->y,'año'    ) . self::p2($d->m,'mes'     ))
        :( $d->m > 0 ? (self::p1($d->m,'mes'    ) . self::p2($d->d,'día'     ))
        :( $d->d > 0 ? (self::p1($d->d,'día'    ) . self::p2($d->h,'hora'    ))
        :( $d->h > 0 ? (self::p1($d->h,'hora'   ) . self::p2($d->i,'minuto'  ))
        :( $d->i > 0 ? (self::p1($d->i,'minuto' ) . self::p2($d->s,'segundo' ))
        :( $d->s > 0 ? (self::p1($d->s,'segundo')) : ( 'Menos de un segundo!!')))))));
    }
  
}
/********
echo date("Y-m-d H:i:s").'<br />';
	

//$fecha = DateTime::createFromFormat('Y-m-d H:i:s', '1962-11-05 05:00:00');
echo HaceCuanto::imprimirTiempo('1962-11-05 05:00:00').'<br />';
echo HaceCuanto::imprimirTiempo('2019-10-05 05:00:00').'<br />';
//echo HaceCuanto::imprimirTiempo('2019-10-15 05:00:00').'<br />';
//echo HaceCuanto::imprimirTiempo('2019-10-21 05:00:00').'<br />';
//echo HaceCuanto::imprimirTiempo('2019-10-21 21:00:00').'<br />';
echo HaceCuanto::imprimirTiempo('2019-10-21 21:39:30').'<br />';
echo HaceCuanto::imprimirTiempo('2019-12-30 21:39:30').'<br />';

echo '<br /><br /><br />';
echo HaceCuanto::plural('camion',1).'<br />';
echo HaceCuanto::plural('camion',3).'<br />';
echo HaceCuanto::plural('bola',1).'<br />';
echo HaceCuanto::plural('bola',7).'<br />';
echo HaceCuanto::plural('CAMION',1).'<br />';
echo HaceCuanto::plural('CAMION',3).'<br />';
echo HaceCuanto::plural('BALA',1).'<br />';
echo HaceCuanto::plural('BALA',7).'<br />';
echo HaceCuanto::plural('perdiz',7).'<br />';
echo HaceCuanto::plural('dedal',7).'<br />';
echo HaceCuanto::plural('silex',7).'<br />';
echo HaceCuanto::plural('viernes',7).'<br />';
*/


$days = array(
  1 => 'Lunes',
  2 => 'Martes',
  3 => 'Miércoles',
  4 => 'Jueves',
  5 => 'Viernes',        
  6 => 'Sábado',        
  7 => 'Domingo',
);

$days_s = array(
  1 => 'Lu',
  2 => 'Ma',
  3 => 'Mi',
  4 => 'Ju',
  5 => 'Vi',        
  6 => 'Sá',        
  7 => 'Do',
);

$months = array(
   1 => 'Enero',
   2 => 'Febrero',
   3 => 'Marzo',
   4 => 'Abril',
   5 => 'Mayo',        
   6 => 'Junio',        
   7 => 'Julio',
   8 => 'Agosto',
   9 => 'Septiembre',
  10 => 'Octubre',
  11 => 'Noviembre',       
  12 => 'Diciembre'
); 
$months_s = array(
   1 => 'Ene',
   2 => 'Feb',
   3 => 'Mar',
   4 => 'Abr',
   5 => 'May',        
   6 => 'Jun',        
   7 => 'Jul',
   8 => 'Ago',
   9 => 'Sep',
  10 => 'Oct',
  11 => 'Nov',       
  12 => 'Dic'
);  

class Timezone {
   var $user_offset;
   // users timezone location
   var $default_offset = -6;
   // default server timezone location, in this case, US Central Time (-6)
   var $difference;
   var $datetime;
  
   function __construct($user_offset, $default_offset = NULL) {
       $this->user_offset = $user_offset;
       if(isset($default_offset)) $this->default_offset = $default_offset;
       $this->difference = ($this->user_offset - $this->default_offset);
       // determine the difference between the users timezone and the servers
   }
  
   function convert($datetime, $format = 'M j, Y \a\t g:iA') {
       $this->difference = (($this->difference >= 0) ? '+' : '') . $this->difference;
       // add a + in front of the difference if positive
       if(eregi('.5', $this->difference)) $difference = substr_replace($this->difference, '', -2, 2) . ' hours +30 minutes';
       // account for a timezone with .5 in it
       else $difference = $this->difference . ' hours';
       $this->datetime = strtotime($datetime);
       $timestamp = strtotime($difference, $this->datetime);
       return date($format, $timestamp);
   }

}

/*
$currentYear = date('Y');
$currentDayNumber = date('j');
$currentDayName = $days[date('N')];
$currentMonthName = $months[date('n')];

printf("Hoy es %s, %d de %s de %s", $currentDayName, 
 $currentDayNumber, $currentMonthName, $currentYear);
*/
/**
function strftime_win32($format, $ts = null) {
   
   global $days,$months,$months_s;
   
   if (!$ts) $ts = time();

   $mapping = array(
       '%A' => $days[date('N',$ts)],
       '%B' => $months[date('n',$ts)],
       '%b' => $months_s[date('n',$ts)],
       '%C' => sprintf("%02d", date("Y", $ts) / 100),
       '%D' => '%m/%d/%y',
       '%e' => sprintf("%' 2d", date("j", $ts)),
       '%h' => '%b',
       '%n' => "\n",
       '%r' => date("h:i:s", $ts) . " %p",
       '%R' => date("H:i", $ts),
       '%G' => date("Y", $ts),
       '%t' => "\t",
       '%T' => '%H:%M:%S',
       '%u' => ($w = date("w", $ts)) ? $w : 7
   );
   $format = str_replace(
       array_keys($mapping),
       array_values($mapping),
       $format
   );
   return strftime($format, $ts);
}
**/
function strftime_win32($format, $ts = null) {
   global $days, $months, $months_s;

   if (!$ts) $ts = time();

   $date = new DateTime();
   $date->setTimestamp($ts);

   $mapping = array(
       '%A' => $days[$date->format('N')],
       '%B' => $months[$date->format('n')],
       '%b' => $months_s[$date->format('n')],
       '%C' => sprintf("%02d", $date->format("Y") / 100),
       '%D' => $date->format('m/d/y'),
       '%e' => sprintf("%' 2d", $date->format("j")),
       '%h' => $date->format('M'),
       '%n' => "\n",
       '%r' => $date->format("h:i:s A"),
       '%R' => $date->format("H:i"),
       '%G' => $date->format("Y"),
       '%t' => "\t",
       '%T' => $date->format("H:i:s"),
       '%u' => $date->format("N"),
   );

   $format = str_replace(
       array_keys($mapping),
       array_values($mapping),
       $format
   );

   return $date->format($format);
}

function fuzzyDate($timestamp) {
   if (!$timestamp) return ''; // Si no hay timestamp, devolvemos una cadena vacía.

   // Idioma actual basado en $_SESSION['lang']
   $lang = $_SESSION['lang'] ?? 'es'; // Por defecto, español

   // Traducciones
   $translations = [
       'es' => [
           'de' => 'de',
           'ayer' => 'Ayer',
           'hoy' => 'Hoy',
           'hace' => 'hace',
           'dias' => 'días',
           'una_semana' => 'una semana',
           'dos_semanas' => 'dos semanas',
           'tres_semanas' => 'tres semanas',
           'un_mes' => 'un mes',
           'dos_meses' => 'dos meses',
           'months' => [
               1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo',
               6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
               10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
           ],
           'days' => [
               1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
               5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
           ]
       ],
       'en' => [
           'de' => 'of',
           'ayer' => 'Yesterday',
           'hoy' => 'Today',
           'hace' => 'ago',
           'dias' => 'days',
           'una_semana' => 'a week',
           'dos_semanas' => 'two weeks',
           'tres_semanas' => 'three weeks',
           'un_mes' => 'a month',
           'dos_meses' => 'two months',
           'months' => [
               1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May',
               6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September',
               10 => 'October', 11 => 'November', 12 => 'December'
           ],
           'days' => [
               1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
               5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
           ]
       ]
   ];

   // Seleccionar traducciones según el idioma
   $t = $translations[$lang] ?? $translations['es'];

   $now = time();
   $diff = $now - $timestamp; // Diferencia en segundos entre ahora y el timestamp.

   if ($timestamp > $now) {
       // Fechas futuras
       return date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " {$t['de']} " . date('Y', $timestamp);
   } elseif ($diff < 86400) {
       // Hoy
       return "<span>{$t['hoy']} " . date('H:i', $timestamp) . '</span>';
   } elseif ($diff < 86400 * 2) {
       // Ayer
       return "<span>{$t['ayer']} " . date('H:i', $timestamp) . '</span>';
   } elseif ($diff < 86400 * 7) {
       // Hace menos de una semana
       $daysAgo = floor($diff / 86400);
       return "<span title='" . $t['days'][date('N', $timestamp)] . ", " . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " " . date('H:i', $timestamp) . "'>{$t['hace']} $daysAgo {$t['dias']}</span>";
   } elseif ($diff < 86400 * 14) {
       // Hace una semana
       return "<span title='" . $t['days'][date('N', $timestamp)] . ", " . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " " . date('H:i', $timestamp) . "'>{$t['hace']} {$t['una_semana']}</span>";
   } elseif ($diff < 86400 * 21) {
       // Hace dos semanas
       return "<span title='" . $t['days'][date('N', $timestamp)] . ", " . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " " . date('H:i', $timestamp) . "'>{$t['hace']} {$t['dos_semanas']}</span>";
   } elseif ($diff < 86400 * 30) {
       // Hace tres semanas
       return "<span title='" . $t['days'][date('N', $timestamp)] . ", " . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " " . date('H:i', $timestamp) . "'>{$t['hace']} {$t['tres_semanas']}</span>";
   } elseif ($diff < 86400 * 60) {
       // Hace un mes
       return "<span title='" . $t['days'][date('N', $timestamp)] . ", " . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " " . date('H:i', $timestamp) . "'>{$t['hace']} {$t['un_mes']}</span>";
   } elseif ($diff < 86400 * 365) {
       // Este año
       return "<span title='" . $t['days'][date('N', $timestamp)] . ", " . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " " . date('H:i', $timestamp) . "'>" . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . '</span>';
   } else {
       // Más de un año
       return "<span title='" . $t['days'][date('N', $timestamp)] . ", " . date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " " . date('H:i', $timestamp) . "'>" . $t['months'][date('n', $timestamp)] . ' ' . date('Y', $timestamp) . '</span>';
   }
}

function fuzzyDateLite($timestamp) {
   if (!$timestamp) return ''; // Si no hay timestamp, devolvemos una cadena vacía.

   // Idioma actual basado en $_SESSION['lang']
   $lang = $_SESSION['lang'] ?? 'es'; // Por defecto, español

   // Traducciones
   $translations = [
       'es' => [
           'de' => 'de',
           'ayer' => 'Ayer',
           'hoy' => 'Hoy',
           'hace' => 'hace',
           'dias' => 'días',
           'una_semana' => 'una semana',
           'dos_semanas' => 'dos semanas',
           'tres_semanas' => 'tres semanas',
           'un_mes' => 'un mes',
           'dos_meses' => 'dos meses',
           'months' => [
               1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo',
               6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
               10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
           ],
           'days' => [
               1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
               5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
           ]
       ],
       'en' => [
           'de' => 'of',
           'ayer' => 'Yesterday',
           'hoy' => 'Today',
           'hace' => 'ago',
           'dias' => 'days',
           'una_semana' => 'a week',
           'dos_semanas' => 'two weeks',
           'tres_semanas' => 'three weeks',
           'un_mes' => 'a month',
           'dos_meses' => 'two months',
           'months' => [
               1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May',
               6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September',
               10 => 'October', 11 => 'November', 12 => 'December'
           ],
           'days' => [
               1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
               5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
           ]
       ]
   ];

   // Seleccionar traducciones según el idioma
   $t = $translations[$lang] ?? $translations['es'];

   $now = time();
   $diff = $now - $timestamp; // Diferencia en segundos entre ahora y el timestamp.

   if ($timestamp > $now) {
       // Fechas futuras
       return date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)] . " {$t['de']} " . date('Y', $timestamp);
   } elseif ($diff < 86400) {
       // Hoy
       return "{$t['hoy']} " . date('H:i', $timestamp);
   } elseif ($diff < 86400 * 2) {
       // Ayer
       return "{$t['ayer']} " . date('H:i', $timestamp);
   } elseif ($diff < 86400 * 7) {
       // Hace menos de una semana
       $daysAgo = floor($diff / 86400);
       return "{$t['hace']} $daysAgo {$t['dias']}";
   } elseif ($diff < 86400 * 14) {
       // Hace una semana
       return "{$t['hace']} {$t['una_semana']}";
   } elseif ($diff < 86400 * 21) {
       // Hace dos semanas
       return "{$t['hace']} {$t['dos_semanas']}";
   } elseif ($diff < 86400 * 30) {
       // Hace tres semanas
       return "{$t['hace']} {$t['tres_semanas']}";
   } elseif ($diff < 86400 * 60) {
       // Hace un mes
       return "{$t['hace']} {$t['un_mes']}";
   } elseif ($diff < 86400 * 365) {
       // Este año
       return date('j', $timestamp) . " {$t['de']} " . $t['months'][date('n', $timestamp)];
   } else {
       // Más de un año
       return $t['months'][date('n', $timestamp)] . ' ' . date('Y', $timestamp);
   }
}


function str2time($strStr, $strPattern = null)
{
   // an array of the valide date characters, see: http://php.net/date#AEN21898
   $arrCharacters = array(
       'd', // day
       'm', // month
       'y', // year, 2 digits
       'Y', // year, 4 digits
       'H', // hours
       'i', // minutes
       's'  // seconds
   );
   // transform the characters array to a string
   $strCharacters = implode('', $arrCharacters);

   // splits up the pattern by the date characters to get an array of the delimiters between the date characters
   $arrDelimiters = preg_split('~['.$strCharacters.']~', $strPattern);
   // transform the delimiters array to a string
   $strDelimiters = quotemeta(implode('', array_unique($arrDelimiters)));

   // splits up the date by the delimiters to get an array of the declaration
   $arrStr    = preg_split('~['.$strDelimiters.']~', $strStr);
   // splits up the pattern by the delimiters to get an array of the used characters
   $arrPattern = preg_split('~['.$strDelimiters.']~', $strPattern);

   // if the numbers of the two array are not the same, return false, because the cannot belong together
   if (count($arrStr) !== count($arrPattern)) {
       return false;
   }

   // creates a new array which has the keys from the $arrPattern array and the values from the $arrStr array
   $arrTime = array();
   for ($i = 0;$i < count($arrStr);$i++) {
       $arrTime[$arrPattern[$i]] = $arrStr[$i];
   }

   // gernerates a 4 digit year declaration of a 2 digit one by using the current year
   if (isset($arrTime['y']) && !isset($arrTime['Y'])) {
       $arrTime['Y'] = substr(date('Y'), 0, 2) . $arrTime['y'];
   }

   // if a declaration is empty, it will be filled with the current date declaration
   foreach ($arrCharacters as $strCharacter) {
       if (empty($arrTime[$strCharacter])) {
           $arrTime[$strCharacter] = date($strCharacter);
       }
   }

   // checks if the date is a valide date
   if (!checkdate($arrTime['m'], $arrTime['d'], $arrTime['Y'])) {
       return false;
   }

   // generates the timestamp
   $intTime = mktime($arrTime['H'], $arrTime['i'], $arrTime['s'], $arrTime['m'], $arrTime['d'], $arrTime['Y']);
   // returns the timestamp
   return $intTime;
}

function win_filetime_to_timestamp ($filetime) {
  $win_secs = substr($filetime,0,strlen($filetime)-7); // divide by 10 000 000 to get seconds
  $unix_timestamp = ($win_secs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds
  return $unix_timestamp;
}
