<?php


class Browser{

  public static function get0(){ 

      $u_agent = $_SERVER['HTTP_USER_AGENT']; 
      $bname = 'Unknown';
      $platform = 'Unknown';
      $version= "";


      // [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.113 Safari/537.36 Edg/81.0.416.58
      // [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.163 Safari/537.36

      //First get the platform?
      if (preg_match('/linux/i', $u_agent)) {
          $platform = $platform = 'Linux';
      } elseif (preg_match('/iPod/i', $u_agent)) {
          $platform = $platform = 'iPod';
      } elseif (preg_match('/iPhone/i', $u_agent)) {
          $platform = 'iPhone';
          if     (preg_match('/OS 10/i', $u_agent)) { $platform .= ' OS 10'; }
          elseif (preg_match('/OS 9/i', $u_agent))  { $platform .= ' OS 9'; }
          elseif (preg_match('/OS 8/i', $u_agent))  { $platform .= ' OS 8'; }
          elseif (preg_match('/OS 7/i', $u_agent))  { $platform .= ' OS 7'; }
          elseif (preg_match('/OS 6/i', $u_agent))  { $platform .= ' OS 6'; }
          elseif (preg_match('/NT 4/i', $u_agent))  { $platform .= ' OS 4'; }
          elseif (preg_match('/NT 3/i', $u_agent))  { $platform .= ' OS 3'; }
          else                                      { $platform .= ' ??'; }
      } elseif (preg_match('/iPad/i', $u_agent)) {
          $platform = 'iPad';
          if     (preg_match('/OS 10/i', $u_agent)) { $platform .= ' OS 10'; }
          elseif (preg_match('/OS 9/i', $u_agent))  { $platform .= ' OS 9'; }
          elseif (preg_match('/OS 8/i', $u_agent))  { $platform .= ' OS 8'; }
          elseif (preg_match('/OS 7/i', $u_agent))  { $platform .= ' OS 7'; }
          elseif (preg_match('/OS 6/i', $u_agent))  { $platform .= ' OS 6'; }
          elseif (preg_match('/NT 4/i', $u_agent))  { $platform .= ' OS 4'; }
          elseif (preg_match('/NT 3/i', $u_agent))  { $platform .= ' OS 3'; }
          else                                      { $platform .= ' ??'; }
      } elseif (preg_match('/Android/i', $u_agent)) {
          $platform = 'Android';
      } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
          $platform = 'Mac';
          if     (preg_match('/OS X 11/i', $u_agent)) { $platform .= ' OS 10'; }
          elseif (preg_match('/OS X 10/i', $u_agent)) { $platform .= ' OS 10'; }
          elseif (preg_match('/OS X 9/i', $u_agent)) { $platform .= ' OS 9'; }
          elseif (preg_match('/OS X 8/i', $u_agent)) { $platform .= ' OS 8'; }
          elseif (preg_match('/OS X 7/i', $u_agent)) { $platform .= ' OS 7'; }
          elseif (preg_match('/OS X 6/i', $u_agent)) { $platform .= ' OS 6'; }
          elseif (preg_match('/OS X 5/i', $u_agent)) { $platform .= ' OS 5'; }
          elseif (preg_match('/OS X 4/i', $u_agent)) { $platform .= ' OS 4'; }
          else                                       { $platform .= ' ??'; }
      } elseif (preg_match('/windows|win32/i', $u_agent)) {

          $platform = 'Windows';
          if     (preg_match('/NT 6.2/i', $u_agent)) { $platform .= ' 8'; }
          elseif (preg_match('/NT 6.3/i', $u_agent)) { $platform .= ' 8.1'; }
          elseif (preg_match('/NT 6.1/i', $u_agent)) { $platform .= ' 7'; }
          elseif (preg_match('/NT 6.0/i', $u_agent)) { $platform .= ' Vista'; }
          elseif (preg_match('/NT 5.1/i', $u_agent)) { $platform .= ' XP'; }
          elseif (preg_match('/NT 5.0/i', $u_agent)) { $platform .= ' 2000'; }
          elseif (preg_match('/NT 10.0/i', $u_agent)) { $platform .= ' 10'; }
          if (preg_match('/WOW64/i', $u_agent) || preg_match('/x64/i', $u_agent)) { $platform .= ' (x64)'; }
      }

      // Next get the name of the useragent yes seperately and for good reason
      if(preg_match('/Edg/i',$u_agent))   { 
          $bname = 'Edge'; 
          $ub = "Edg"; 
      }else if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))   { 
          $bname = 'Internet Explorer'; 
          $ub = "MSIE"; 
      } elseif(preg_match('/Trident/i',$u_agent))  { // this condition is for IE11
          $bname = 'Internet Explorer'; 
          $ub = "rv"; 
      } elseif(preg_match('/Firefox/i',$u_agent))  { 
          $bname = 'Mozilla Firefox'; 
          $ub = "Firefox"; 
      } elseif(preg_match('/Chrome/i',$u_agent)) { 
          $bname = 'Google Chrome'; 
          $ub = "Chrome"; 
      } elseif(preg_match('/Safari/i',$u_agent)) { 
          $bname = 'Apple Safari'; 
          $ub = "Safari"; 
      } elseif(preg_match('/Opera/i',$u_agent))  { 
          $bname = 'Opera'; 
          $ub = "Opera"; 
      } elseif(preg_match('/Netscape/i',$u_agent)) { 
          $bname = 'Netscape'; 
          $ub = "Netscape"; 
      } 
      
      // finally get the correct version number
      // Added "|:"
      $known = array('Version', $ub, 'other');
      $pattern = '#(?<browser>' . join('|', $known) .
       ')[/|: ]+(?<version>[0-9.|a-zA-Z.]*)#';
      if (!preg_match_all($pattern, $u_agent, $matches)) {
          // we have no matching number just continue
      }

      // see how many we have
      $i = count($matches['browser']);
      if ($i != 1) {
          //we will have two since we are not using 'other' argument yet
          //see if version is before or after the name
          if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
              $version= $matches['version'][0];
          } else {
              $version= $matches['version'][1];
          }
      } else {
          $version= $matches['version'][0];
      }

      // check if we have a number
      if ($version==null || $version=="") {$version="?";}

      return array(
          'userAgent' => $u_agent,
          'name'      => $bname,
          'version'   => $version,
          'platform'  => $platform,
          'pattern'   => $pattern
      );
  }  





    public static function get($userAgent = null) {
        // Si no se proporciona user agent, usar el actual
        if ($userAgent === null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        
        $navegador = 'Desconocido';
        $sistema = 'Desconocido';
        
        // Detectar bots primero
        $bots = [
            'Googlebot' => '/Googlebot\/([0-9.]+)/',
            'Bingbot' => '/bingbot\/([0-9.]+)/',
            'Slurp' => '/Slurp\/([0-9.]+)/',
            'DuckDuckBot' => '/DuckDuckBot\/([0-9.]+)/',
            'Baiduspider' => '/Baiduspider\/([0-9.]+)/',
            'YandexBot' => '/YandexBot\/([0-9.]+)/',
            'facebookexternalhit' => '/facebookexternalhit\/([0-9.]+)/',
            'Twitterbot' => '/Twitterbot\/([0-9.]+)/',
            'LinkedInBot' => '/LinkedInBot\/([0-9.]+)/',
            'WhatsApp' => '/WhatsApp\/([0-9.]+)/',
            'TelegramBot' => '/TelegramBot\/([0-9.]+)/',
        ];
        
        foreach ($bots as $botName => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $navegador = $botName;
                $sistema = isset($matches[1]) ? $botName . ' ' . $matches[1] : $botName;
                return [$navegador, $sistema];
            }
        }
        
        // Si contiene "bot" pero no está en la lista específica
        if (stripos($userAgent, 'bot') !== false || stripos($userAgent, 'crawler') !== false || stripos($userAgent, 'spider') !== false) {
            // Intentar extraer el nombre del bot
            if (preg_match('/([a-zA-Z]+bot|[a-zA-Z]+crawler|[a-zA-Z]+spider)[\/\s]?([0-9.]*)/i', $userAgent, $matches)) {
                $navegador = $matches[1];
                $sistema = isset($matches[2]) && $matches[2] ? $matches[1] . ' ' . $matches[2] : $matches[1];
            } else {
                $navegador = 'Bot';
                $sistema = 'Bot genérico';
            }
            return [$navegador, $sistema];
        }
        
        // Detectar sistema operativo
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $version = $matches[1];
            $windowsVersions = [
                '10.0' => 'Windows 11/10',
                '6.3' => 'Windows 8.1',
                '6.2' => 'Windows 8',
                '6.1' => 'Windows 7',
                '6.0' => 'Windows Vista',
                '5.1' => 'Windows XP',
            ];
            $sistema = $windowsVersions[$version] ?? 'Windows ' . $version;
        } elseif (preg_match('/Mac OS X ([0-9_]+)/', $userAgent, $matches)) {
            $version = str_replace('_', '.', $matches[1]);
            $sistema = 'macOS ' . $version;
        } elseif (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
            $version = str_replace('_', '.', $matches[1]);
            $sistema = 'iOS ' . $version;
        } elseif (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            $sistema = 'Android ' . $matches[1];
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $sistema = 'Linux';
        } elseif (stripos($userAgent, 'Ubuntu') !== false) {
            $sistema = 'Ubuntu';
        } elseif (stripos($userAgent, 'CrOS') !== false) {
            $sistema = 'Chrome OS';
        }
        
        // Detectar navegador
        if (preg_match('/Edg\/([0-9.]+)/', $userAgent, $matches)) {
            $navegador = 'Edge ' . $matches[1];
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            if (stripos($userAgent, 'OPR') !== false) {
                if (preg_match('/OPR\/([0-9.]+)/', $userAgent, $operaMatches)) {
                    $navegador = 'Opera ' . $operaMatches[1];
                }
            } else {
                $navegador = 'Chrome ' . $matches[1];
            }
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $navegador = 'Firefox ' . $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            if (stripos($userAgent, 'Chrome') === false) {
                // Intentar obtener la versión real de Safari
                if (preg_match('/Version\/([0-9.]+)/', $userAgent, $versionMatches)) {
                    $navegador = 'Safari ' . $versionMatches[1];
                } else {
                    $navegador = 'Safari';
                }
            }
        } elseif (preg_match('/MSIE ([0-9.]+)/', $userAgent, $matches)) {
            $navegador = 'Internet Explorer ' . $matches[1];
        } elseif (preg_match('/Trident.*rv:([0-9.]+)/', $userAgent, $matches)) {
            $navegador = 'Internet Explorer ' . $matches[1];
        }
        
        return ['name'      =>$navegador, 'platform'  => $sistema];
              /*
      return array(
          'userAgent' => $u_agent,
          'name'      => $bname,
          'version'   => $version,
          'platform'  => $platform,
          'pattern'   => $pattern
      );
       */
    }

    // Ejemplo de uso:
    // $info = detectarNavegadorYSistema();
    // echo "Navegador: " . $info[0] . "\n";
    // echo "Sistema: " . $info[1] . "\n";

    // Para probar con un user agent específico:
    // $info = detectarNavegadorYSistema('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
























}


