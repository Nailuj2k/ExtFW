<?php
//die('404');

$extfw_file = 'extfw_base';
$extfw_installer = 'extfw_installer';
$extfw_update = 'extfw_update';
$extfw_installer_version = '20260410';
$host_extfw = 'https://software.extralab.net/';
$extfw_version = file_get_contents( 'https://tienda.extralab.net/version/html');
$php_ver = phpversion();
$php_os = php_uname('s').' '.php_uname('r'); //PHP_OS.' '.PHP_OS_FAMILY; // https://stackoverflow.com/questions/21129737/how-do-i-interpret-the-output-of-php-uname
$server = $_SERVER['SERVER_SOFTWARE']; //print_r($_SERVER,true);  //['SERVER_SOFTWARE'];
/*
Ubuntu:
Linux-4.9.184-linuxkit-x86_64-with-Ubuntu-18.04-bionic

Debian:
Linux-4.14.117-grsec-grsec+-x86_64-with-debian-buster-sid
Linux hlaweba8 4.19.0-14-amd64 #1 SMP Debian 4.19.171-2 (2021-01-30) x86_64 // Buster HLA

Centos:
Linux-3.10.0-957.1.3.el7.x86_64-x86_64-with-centos-7.6.1810-Core
Linux 3.10.0-957.10.1.el7.x86_64  // Centos Extralab

Mac OS X:
Darwin-17.7.0-x86_64-i386-64bit



*/
$advanced = isset($_GET['mode']) && $_GET['mode']=='advanced';

Class INSTALLER {

    public static function getOSInformation() {
        if (false == function_exists("shell_exec") || false == is_readable("/etc/os-release")) { return null; }
        $os         = shell_exec('cat /etc/os-release');
        $listIds    = preg_match_all('/.*=/', $os, $matchListIds);
        $listIds    = $matchListIds[0];
        $listVal    = preg_match_all('/=.*/', $os, $matchListVal);
        $listVal    = $matchListVal[0];
        array_walk($listIds, function(&$v, $k){ $v = strtolower(str_replace('=', '', $v));  });
        array_walk($listVal, function(&$v, $k){ $v = preg_replace('/=|"/', '', $v);  });
        return array_combine($listIds, $listVal);
    }

    public static function unzip($dir,$file, $verbose = false){
      if ($zip = zip_open($dir.$file.".zip")) {
        if ($zip) {
          //mkdir($dir.$file);
          while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip,$zip_entry,"r")) {
              $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
              $dir_name = dirname(zip_entry_name($zip_entry));
              if ($dir_name != ".") {
                $dir_op = './'; //$dir.$file."/";
                foreach ( explode("/",$dir_name) as $k) {
                  $dir_op = $dir_op . $k;
                  if (is_file($dir_op)) unlink($dir_op);
                  if (!is_dir($dir_op)) mkdir($dir_op);
                  $dir_op = $dir_op . "/" ;
                }
              }
              if ($verbose) echo "extracting: ".zip_entry_name($zip_entry)."<br>";
              $fp=@fopen("./".zip_entry_name($zip_entry),"w");
              @fwrite($fp,$buf);
              zip_entry_close($zip_entry);
            }
            else return false;
          }
          zip_close($zip);
        }
      } else return false;
      return true;
    }

    /**
     * Create a directory structure recursively
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.2
     * @link        http://aidanlister.com/2004/04/recursively-creating-directory-structures/
     * @param       string   $pathname    The directory structure to create
     * @return      bool     Returns TRUE on success, FALSE on failure
     */
    public static function mkdirr($pathname, $mode = 0777){
        if (is_dir($pathname) || empty($pathname)) return true;  // Check if directory already exists
        $pathname = str_replace(array('/', ''), DIRECTORY_SEPARATOR, $pathname);
        if (is_file($pathname)) {    // Ensure a file does not already exist with the same name
            trigger_error('mkdirr() File exists', E_USER_WARNING);
            return false;
        }
        $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR)); // Crawl up the directory tree
        if (self::mkdirr($next_pathname, $mode)) {
            if (!file_exists($pathname))   return mkdir($pathname, $mode);
        }
        return false;
    }

    /**
     * Delete a file, or a folder and its contents (recursive algorithm)
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.3
     * @link        http://www.aidanlister.com/2004/04/recursively-deleting-a-folder-in-php/
     * @param       string   $dirname    Directory to delete
     * @return      bool     Returns TRUE on success, FALSE on failure
     */
    public static function rmdirr($dirname,$verbose=false){
        if (!file_exists($dirname)) { if ($verbose) echo " Directory $dirname does not exists<br>"; return false;}// Sanity check
        if (is_file($dirname) || is_link($dirname)) {  // Simple delete for a file
          if ($verbose) echo " Deleting $dirname as file<br />";
          unlink($dirname);
          return true;
        }
        if ($verbose) echo "deleting: $dirname<br />";
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) { // Loop through the folder
            if ($entry == '.' || $entry == '..') { continue; } // Skip pointers
            self::rmdirr($dirname . DIRECTORY_SEPARATOR . $entry,$verbose); // Recurse
        }
        $dir->close();   // Clean up
        if ($verbose) echo " OK<br>";
        return rmdir($dirname);
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://www.aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @return      bool     Returns TRUE on success, FALSE on failure
     */
    public static function copyr($source, $dest, $verbose=false){
        if (!file_exists($source)) { if ($verbose) echo "Directory $source does not exists<br>"; return false;  }
        if ($verbose) echo "copying: $source to $dest";
        if (is_link($source)) { if ($verbose) echo " OK<br>"; return symlink(readlink($source), $dest); }   // Check for symlinks
        if (is_file($source)) {
           if ($verbose) echo " OK<br>";
           return @copy($source, $dest);    // Simple copy for a file
        }
        if (!is_dir($dest))  { @mkdir($dest);  } // Make destination directory
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {  // Loop through the folder
            if ($entry == '.' || $entry == '..') {  continue;  }  // Skip pointers
            $ok = self::copyr("$source/$entry", "$dest/$entry"); // Deep copy directories
            if (!$ok) {if ($verbose) echo " FAIL<br/>";return false;}
        }
        $dir->close(); // Clean up
        if ($verbose) echo " OK<br>";
        return true;
    }

    /**
     * Calculate the size of a directory by iterating its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.2.0
     * @link        http://aidanlister.com/2004/04/calculating-a-directories-size-in-php/
     * @param       string   $directory    Path to directory
     */

    public static function dirsizexx($path){
        $size = 0;    // Init
        if (substr($path, -1, 1) !== DIRECTORY_SEPARATOR) $path .= DIRECTORY_SEPARATOR;  // Trailing slash
        if (is_file($path)) return filesize($path); elseif (!is_dir($path)) return false; // Sanity check
        $queue = array($path);
        for ($i = 0, $j = count($queue); $i < $j; ++$i){   // Iterate queue
            $parent = $i;
            if (is_dir($queue[$i]) && $dir = @dir($queue[$i])) { // Open directory

                $subdirs = array();
                while (false !== ($entry = $dir->read())) {
                    if ($entry == '.' || $entry == '..') continue;   // Skip pointers
                    $path = $queue[$i] . $entry;   // Get list of directories or filesizes
                    if (is_dir($path)) {
                        $path .= DIRECTORY_SEPARATOR;
                        $subdirs[] = $path;
                    } elseif (is_file($path)) {
                        $size += filesize($path);
                    }
                }
                unset($queue[0]);
                $queue = array_merge($subdirs, $queue);// Add subdirectories to start of queue
                $i = -1;  // Recalculate stack size
                $j = count($queue);
                $dir->close();   // Clean up
                unset($dir);
            }
        }
        return $size;
    }

    public static function mover($source, $dest, $verbose=false){if (self::copyr($source, $dest, $verbose)) rmdirr($source,$verbose);}
    public static function mkdirs($dir, $verbose=false){ if (!file_exists($dir)) { mkdir($dir); if ($verbose) echo " mkdir $dir OK<br>"; }else{ if ($verbose) echo "dir $dir already exists<br />"; }}

    /**
     * Replicate the output from `mysql --html`
     *
     * Draws a HTML table from a query resource
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.3
     * @link        http://aidanlister.com/2004/04/outputing-a-mysql-result-in-a-html-table/
     * @param       array   $result    The result of a mysql_query
     * @param       string  $null      Text to replace empty values with
     */
     /*
    function mysql_draw_table($result, $null = '&nbsp;'){
        // Sanity check
        if (!is_resource($result) ||
            substr(get_resource_type($result), 0, 5) !== 'mysql') {
            return false;
        }
        $out = "<table>\n";
        $out .= "\t<tr>";  // Table header
        for ($i = 0, $ii = mysql_num_fields($result); $i < $ii; $i++) $out .= '<th>'.mysql_field_name($result, $i).'</th>';
        $out .= "</tr>\n";
        for ($i = 0, $ii = mysql_num_rows($result); $i < $ii; $i++) {    // Table content
            $out .= "\t<tr>";
            $row = mysql_fetch_row($result);
            foreach ($row as $value) {  // Display empty cells
                $value = (empty($value) && ($value != '0')) ?  $null :   htmlspecialchars($value);
                $out .= '<td>' . $value . '</td>';
            }
            $out .= "</tr>\n";
        }
        $out .= "</table>\n";
        echo $out;
    }
    **/
    /**
     * Chop a string into a smaller string.
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.1.0
     * @link        http://aidanlister.com/2004/04/creating-a-string-exerpt-elegantly/
     * @param       mixed  $string   The string you want to shorten
     * @param       int    $length   The length you want to shorten the string to
     * @param       bool   $center   If true, chop in the middle of the string
     * @param       mixed  $append   String appended if it is shortened
     */
    public static function str_chop($string, $length = 60, $center = false, $append = null){
        if ($append === null) {  $append = ($center === true) ? ' ... ' : ' ...'; }  // Set the default append string
        $len_string = strlen($string); // Get some measurements
        $len_append = strlen($append);
        if ($len_string > $length) {  // If the string is longer than the maximum length, we need to chop it
            if ($center === true) { // Checf we want to chop it in half
                $len_start = $length / 2;  // Get the lengths of each segment
                $len_end = $len_start - $len_append;
                $seg_start = substr($string, 0, $len_start); // Get each segment
                $seg_end = substr($string, $len_string - $len_end, $len_end);
                $string = $seg_start . $append . $seg_end;   // Stick them together
            } else {
                $string = substr($string, 0, $length - $len_append) . $append;// Otherwise, just chop the end off
            }
        }
        return $string;
    }

    public static function create_php_file($filename,$contenido){
      if (file_exists($filename)){
        //echo "El archivo: <i>$filename</i> ya existe!";
      }else {
        if($hfp = fopen($filename,'w+')){
          $contenido = '<'."?php\n".stripslashes($contenido)."\n";
          fwrite($hfp,$contenido);
          //echo "Archivo <i>$filename</i> creado!";
          fclose($hfp);
        }else{
         // echo "No ha sido posible crear el archivo: <i>$filename</i>!";
        }
      }
    }

    public static function testrmdir(){
      // Create a directory and file tree
      mkdir('testdelete');
      mkdir('testdelete/one-a');
      touch('testdelete/one-a/testfile');
      mkdir('testdelete/one-b');
      // Add some hidden files for good measure
      touch('testdelete/one-b/.hiddenfile');
      mkdir('testdelete/one-c');
      touch('testdelete/one-c/.hiddenfile') ;
      // Add some more depth
      mkdir('testdelete/one-c/two-a');
      touch('testdelete/one-c/two-a/testfile');
      mkdir('testdelete/one-d/');
      // Test that symlinks are not followed
      mkdir('testlink');
      touch('testlink/testfile');
      symlink(getcwd() . '/testlink/testfile', 'testdelete/one-d/my-symlink');
      symlink(getcwd() . '/testlink', 'testdelete/one-d/my-symlink-dir');
      // Run the actual delete
      $status = self::rmdirr('testdelete');
      // Check if we passed the test
      if ($status === true && !file_exists('testdelete') &&  file_exists('testlink/testfile')) {
        return 'Test rmdir PASSED';
        self::rmdirr('testlink');
      } else {
        return 'Test rmdir  FAILED';
      }
    }

    public static function testcopydir(){
        // Create a directory and file tree
        mkdir('testcopy');
        mkdir('testcopy/one-a');
        touch('testcopy/one-a/testfile');
        mkdir('testcopy/one-b');
        // Add some hidden files for good measure
        touch('testcopy/one-b/.hiddenfile');
        mkdir('testcopy/one-c');
        touch('testcopy/one-c/.hiddenfile');
        // Add some more depth
        mkdir('testcopy/one-c/two-a');
        touch('testcopy/one-c/two-a/testfile');
        mkdir('testcopy/one-d/');
        // Test that symlinks are created properly
        mkdir('testlink');
        touch('testlink/testfile');
        symlink(getcwd() . '/testlink/testfile', 'testcopy/one-d/my-symlink');
        symlink(getcwd() . '/testlink', 'testcopy/one-d/my-symlink-dir');
        symlink('../', 'testcopy/one-d/my-symlink-relative');
        //$status = self::copyr('testcopy', 'testcopy-copy');
        if (         file_exists('testcopy')
             //   && file_exists('testcopy-copy')
             //   && file_exists('testcopy-copy/one-b/.hiddenfile')
             //   && file_exists('testcopy-copy/one-c/two-a/testfile')
             // && is_link('testcopy-copy/one-d/my-symlink-relative')
             // && (readlink('testcopy-copy/one-d/my-symlink-relative') == '../')
             // && is_link('testcopy-copy/one-d/my-symlink')
           ) {
                    sleep(1);

                    @unlink('testcopy/one-d/my-symlink');
                    @unlink('testcopy/one-d/my-symlink-dir');
                    @unlink('testcopy/one-d/my-symlink-relative');
                    @unlink('testcopy/one-a/testfile');
                    @unlink('testcopy/one-b/.hiddenfile');
                    @unlink('testcopy/one-c/.hiddenfile');
                    @unlink('testcopy/one-c/two-a/testfile');
                    @unlink('testlink/testfile');

                    self::rmdirr('testlink');
                    self::rmdirr('testcopy');
                    // self::rmdirr('testcopy-copy');
                    return "Test copydir PASSED";
                } else {
                    return "Test copydir FAILED";
                }
    }

    public static function download ($file_source, $file_target, $verbose=false){
        $stream_opts = [ "ssl" => [ "verify_peer"=>false, "verify_peer_name"=>false, ] ];
        $file_source = str_replace(' ', '%20', html_entity_decode($file_source)); // fix url format
        if (file_exists($file_target)) { @chmod($file_target, 0777); } // add write permission
        $response = file_get_contents($file_source,     false, stream_context_create($stream_opts));
        if(is_writable(dirname(__FILE__))){
            file_put_contents($file_target, $response);
            if (file_exists($file_target) && filesize($file_target)>10) {
                  $messages['msg'][] =  "OK";
            }else{
                  $messages['msg'][] = "FAIL (Unknown error)";
                  $messages['error'] =  1;
            }
        }else{
            $messages['msg'][] = dirname(__FILE__).' no tiene permisos de escritura';
            $messages['msg'][] =   "<pre># cd /var/www\n"
                                 . "# find /var/www -type d -exec chmod 0755 {} \;\n"
                                 . "# find /var/www -type f -exec chmod 0644 {} \;\n"
                                 . "# chown www-data:www-data html -R\n'</pre>";

            $messages['error'] =  1;
        }
        return $messages;
    }

    public static function setErrorLevel($level){
        if($level){
            error_reporting(E_ALL & ~E_NOTICE);
            ini_set('error_reporting',E_ALL & ~E_NOTICE );
            ini_set('display_errors', 1);
        }else{
            error_reporting(0);
            ini_set('error_reporting',0 );
            ini_set('display_errors', 0);
        }
    }

    public static function makePass() {
      $makepass="";
      $syllables="er,in,tia,wol,fe,pre,vet,jo,nes,al,len,son,cha,ir,ler,bo,ok,tio,nar,sim,ple,bla,ten,toe,cho,co,lat,spe,ak,er,po,co,lor,pen,cil,li,ght,wh,at,the,he,ck,is,mam,bo,no,fi,ve,any,way,pol,iti,cs,ra,dio,sou,rce,sea,rch,pa,per,com,bo,sp,eak,st,fi,rst,gr,oup,boy,ea,gle,tr,ail,bi,ble,brb,pri,dee,kay,en,be,se";
      $syllable_array=explode(",", $syllables);
      srand((double)microtime()*1000000);
      for ($count=1;$count<=2;$count++) {
        if (rand()%10 == 1) {
          $makepass .= sprintf("%0.0f",(rand()%50)+1);
        } else {
          $makepass .= sprintf("%s",$syllable_array[rand()%62]);
        }
      }
      return($makepass);
    }
    public static function makePrefix($len=5) {
      return substr(str_shuffle("qwertyuiopasdfghjklzxcvbnm"),0,$len);
    }
}

INSTALLER::setErrorLevel(false);

$result = array();
$result['error'] = 0;
$result['msg'] = 'ok';

$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : '';

switch ($op) {
    case 'install':
        $result = array();
        $result['msg'] = 'option disabled';

        if (file_exists('configuration.php') && file_exists('index.php')) {
            $result['msg'] = 'El sistema ya está instalado';
            $result['error'] = 1;
        } else {
            if ($_POST['dbtype'] == 'sqlite') {
                $sqlite_dbname = INSTALLER::makePrefix(10);
                if (file_exists('demo.sqlite')) {
                    rename('demo.sqlite', $sqlite_dbname . '.sqlite');
                }
                $_POST['dbname'] = $sqlite_dbname;
            }

            // Sanitize user input before writing to configuration file
            $sitename = addslashes($_POST['sitename']);
            $prefix = addslashes($_POST['prefix']);
            $email = addslashes($_POST['email']);
            $dbtype = addslashes($_POST['dbtype']);
            $dbhost = addslashes($_POST['dbhost']);
            $dbuser = addslashes($_POST['dbuser']);
            $dbpass = addslashes($_POST['dbpass']);
            $dbname = addslashes($_POST['dbname']);

            $configuration_content = '
            /* * * *
             *
             * Fichero donde se parametriza la configuración de la aplicacion
             *
             *
             * */

            $cfg[\'title\']    = \'' . $sitename . '\';
            $cfg[\'description\'] = \'description\';
            $cfg[\'keywords\']    = \'keywords\';
            $cfg[\'prefix\'] = \'' . $prefix . '\';
            $cfg[\'email\']  = \'' . $email . '\';
            $cfg[\'debug\']  = false;
            $cfg[\'timezone\'] = \'Europe/Madrid\';  // Valid timezones list: http://php.net/manual/timezones.php


            $cfg[\'db\'][\'type\']  = \'' . $dbtype . '\';
            $cfg[\'db\'][\'host\']  = \'' . $dbhost . '\';
            $cfg[\'db\'][\'user\']  = \'' . $dbuser . '\';
            $cfg[\'db\'][\'pass\']  = \'' . $dbpass . '\';
            $cfg[\'db\'][\'name\']  = \'' . $dbname . '\';
            $cfg[\'auth\']   = \'mysql\';  //\'sqlite\' ,\'mysql\' \'demo\', \'internal\';  // \'ldap\'
            $cfg[\'redis\']  = false;
            $cfg[\'repo\'][\'url\']  = \'https://software.extralab.net\';
            $cfg[\'themes\']  = array(\'clear\',\'simple\',\'default\');
            $cfg[\'langs\']   = array(\'es\',\'en\',\'es_ca\');  //,\'es_mu\');
            $cfg[\'production\']  = false;
            $cfg[\'enable_themes\']  = true;
            $cfg[\'default_lang\']   = \'es\';
            $cfg[\'default_theme\']  = \'default\';
            $cfg[\'default_module\'] = \'page\';  //\'home\';
          //$cfg[\'default_page\'] = false;  //\'intro\';
            $cfg[\'module_security\'] = false;
            define(\'PRIVATE_DIR\'         , ' . (dirname($_SERVER['DOCUMENT_ROOT']) == '/var/www' ? 'ROOT_DIR.\'/media\'' : 'dirname(ROOT_DIR)') . ' );
            define(\'SCRIPT_DIR\'          , str_replace(\'/index.php\', \'\', $_SERVER[\'SCRIPT_NAME\'] ) );
            define(\'MODULE_SHOP\'         , false);
            define(\'WYSIWYG_EDITOR\'      , \'extfw\');
            define(\'CODE_EDITOR\'         , \'monaco\');
            define(\'CACHE_DIR\'           , PRIVATE_DIR.\'/cache\' );
            define(\'ERROR_LEVEL\'         , $cfg[\'production\'] ? 0 : E_ALL );
            ';

            INSTALLER::create_php_file('configuration.php', $configuration_content);
            $ok = INSTALLER::unzip("", $extfw_file, false);
            sleep(2);

            INSTALLER::mkdirs('media', false);
            INSTALLER::mkdirs('media/files', false);
            INSTALLER::mkdirs('media/avatars', false);
            sleep(2);

            $result['error'] = 0;
        }
        //TODO Enviar un email con la contraseña
        break;

    case 'check_db':
        //    0 Oki !!
        // 1044 Acceso denegado a la database
        // 1045 Acceso denegado user o passw
        // 2005 unknown host
        if ($_POST['type'] == 'mysql') {
            try {
                $connection = new PDO("mysql:host={$_POST['host']};dbname={$_POST['name']}", $_POST['user'], $_POST['pass']);
                $result['ok'] = 'ok';
            } catch (PDOException $e) {
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                $result['ok'] = $result['code'] == 0 ? 'ok' : 'ko';
            }

            if ($result['ok'] != 'ok' && $_POST['user'] && $_POST['pass'] && $_POST['name']) {
                $result['sql'] = "CREATE DATABASE `{$_POST['name']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; \n"
                    . "CREATE USER {$_POST['user']}@'%' IDENTIFIED BY '{$_POST['pass']}'; \n"
                    . "GRANT USAGE ON *.* TO {$_POST['user']}@'%' IDENTIFIED BY '{$_POST['pass']}' \n"
                    . "WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0  \n"
                    . "     MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ; \n"
                    . "GRANT ALL PRIVILEGES ON {$_POST['name']}.* TO {$_POST['user']}@'%' WITH GRANT OPTION ; \n";
            }
        } else if ($_POST['type'] == 'sqlite') {
            $dbname = 'demo';
            try {
                $connection = new PDO("sqlite:{$dbname}.sqlite");
                $result['ok'] = 'ok';
            } catch (PDOException $e) {
                $result['code'] = $e->getCode();
                $result['msg'] = ucfirst($e->getMessage()) . '. Necesita instalar SQLite';
                $result['ok'] = 'ko';
            }
        } else {
            $result['code'] = 'ok';
            $result['ok'] = 'ok';
            $result['msg'] = 'No type selected';
        }
        break;

    case 'update':
        sleep(2);
        $result = INSTALLER::download($_POST['host'] . $_POST['file'] . '.zip', $_POST['file'] . '.zip', false);
        if ($result['error'] < 1) {
            $ok = INSTALLER::unzip("", $_POST['file'], false);
            if (!$ok) {
                $result['error'] == 1;
                $result['msg'] = 'Error ' . __LINE__;
            }
        }
        sleep(2);
        break;

    case 'download':
        sleep(4);
        $result = INSTALLER::download($_POST['host'] . $_POST['file'] . '.zip', $_POST['file'] . '.zip', false);
        break;

    case 'delete':
        INSTALLER::rmdirr('install.php', true);
        $result['msg'] = '<h4>Script de instalación eliminado.</h4><p>Bueno, no. Ésta opción está desactivada. :)</p>';
        break;

    case 'deleteextfw':
        INSTALLER::rmdirr('_classes_', true);
        INSTALLER::rmdirr('_i18n_', true);
        INSTALLER::rmdirr('_images_', true);
        INSTALLER::rmdirr('_includes_', true);
        INSTALLER::rmdirr('_js_', true);
        INSTALLER::rmdirr('_lib_', true);
        INSTALLER::rmdirr('_modules_', true);
        INSTALLER::rmdirr('_outputs_', true);
        INSTALLER::rmdirr('_themes_', true);
        INSTALLER::rmdirr('_plugins_', true);
        INSTALLER::rmdirr('vendor', true);
        INSTALLER::rmdirr('media', true);
        INSTALLER::rmdirr('.htaccess', true);
        INSTALLER::rmdirr('index.php', true);
        INSTALLER::rmdirr('favicon.ico', true);
        INSTALLER::rmdirr('configuration.php', true);
        INSTALLER::rmdirr('configuration.mysql.php', true);
        $result['msg'] = '<h4>Sistema eliminado.</h4>';
        break;

    case 'refactor':
        $result['msg']='refactoring ...';

       if(file_exists('configuration.php')) include('configuration.php'); 

       try{
            $connection = new PDO("mysql:host={$cfg['db']['host']};dbname={$cfg['db']['name']}", $cfg['db']['user'], $cfg['db']['pass']);
            $result['ok'] =  'ok';
        } catch (PDOException $e){
            $result['code'] = $e->getCode();
            $result['msg'] = $e->getMessage();
            $result['ok'] = $result['code']==0 ? 'ok' : 'ko'; 
        }
         
        function sqlexec($sql){
       //     return $sql;

            global $connection;
            $connection->exec($sql);
            return $connection->errorInfo();


        }

        $cfg['prefix']='astea';

        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_acl_item_roles TO '.TB_ACL_ITEM_ROLES);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_acl_permissions TO '.TB_ACL_PERMISSIONS);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_acl_roles TO '.TB_ACL_ROLES);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_acl_role_perms TO '.TB_ACL_ROLE_PERMS);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_acl_user_perms TO '.TB_ACL_USER_PERMS);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_acl_user_roles TO '.TB_ACL_USER_ROLES);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_cc TO '.TB_CC);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_item TO '.TB_ITEM);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_lang TO '.TB_LANG);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_str TO '.TB_STR);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_user TO '.TB_USER);
        $result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_user_contacts TO '.TB_USER_CONTACTS);
      //$result['messages'][] = sqlexec('RENAME TABLE '.$cfg['prefix'].'_user_files TO '.TB_USER_FILES);
         
        $result['msg'] = implode('<br>',$result['messages']);

        break;


    case 'test':
        INSTALLER::setErrorLevel(false);
        $result['msg'] = INSTALLER::testrmdir() . '<br />' . INSTALLER::testcopydir();
        break;

    default:
        // This is not an API call, so we render the HTML page.
        // We check if there is an active operation to avoid sending headers twice.
        if ($op !== '') {
            // If it's an unknown operation, maybe return an error.
            // For now, we just break and let the HTML render.
        }
        break;
}

// If it's an API call, send JSON response and exit.
if ($op !== '') {
    Header('Expires: 0');
    Header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    Header('Content-type: application/json');
    Header('charset: utf-8');
    echo json_encode($result);
    exit();
}

// If we are here, it means no 'op' was provided, so we render the HTML page.
?><!doctype html>
<!--[if (IE 8)&!(IEMobile)]><html class="no-js lt-ie9" lang="en"><![endif]-->
<!--[if (gte IE 9)| IEMobile |!(IE)]><!--><html class="no-js" lang="en"><!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>ExtFW <?=$extfw_version?> Install Script</title>
    <meta name="HandheldFriendly" content="True" />
    <meta name="MobileOptimized" content="320" />
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
    <meta http-equiv="cleartype" content="on" />
    <meta name="application-name" content="ExtFW Framework" />
    <style>
        *{box-sizing: border-box;}
        body {font-size: 1.4rem; overflow: auto;-webkit-font-smoothing: antialiased; }
        body { background: linear-gradient(to right, #f7f7f766, #e6eff444);}
        body, html {width: 100%;margin:0;display:block;font-size: 62.5%;line-height: 1.65;-webkit-tap-highlight-color: transparent;}
        header,section,footer,h1,h2,h3,h4,h5,div,p,li,label,input,select,textarea,dt,dd,a,i,b,strong,strike{-ms-text-size-adjust:100%;-webkit-text-size-adjust: 100%;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Droid Sans,Helvetica Neue,sans-serif;/*letter-spacing:.2px;*/}
        h1, h2, h3, h4, h5, h6 {line-height: 1.15em;font-weight: 300;text-indent:-1px;font-size: 4rem;}
        body, h1, h2, h3, h4, h5, h6 { text-rendering: optimizeLegibility;}
        ::selection { background: #f14586;color:white;}
        ul,ol{text-align:left;margin-left:0px;}
        ol { list-style: none;counter-reset: the-counter;padding-left:5px;}
        li {  counter-increment: the-counter; margin-left:0px;}
        li::before {  content: counter(the-counter);background: #dfdfdf;width:2.4rem;height:2.4rem;border-radius:50%; display:inline-block;line-height:2.2rem;color:#5699b6;text-align:center;margin-right: 0.5rem;}
        ol ol li::before {background: #DE51FF;}
        ol ol ol li::before {background: #EE9EFF;}
        p{text-align:justify;}
        a{color:#0084c4;text-decoration:none;}
        a:hover{color:#ff006c;}
        .wrap {display: flex;-webkit-box-direction: normal;flex-direction: column;max-width: 700px;font-size: 1.9rem;line-height: 1.5em;font-weight: 300;text-align: center;margin: 0 auto;}
        .inner{display: flex;-webkit-box-direction: normal;flex-direction: column;max-width: 700px;}
        footer{width: 100%;text-align: center;line-height:70px;font-size: 1.9rem;color:#e4e6e7;font-weight: 300;margin: 0 auto;background: #15212a;position:fixed;bottom:0px;}
        .content{min-height:600px;}
        .content .border-box {position: relative;margin: 10px auto;padding: 10px 40px;max-width: 550px;width: 100%;border: 1px solid #dae1e3;background: #fff;border-radius: 5px;text-align: left;box-shadow: 0 20px 45px -10px rgba(0,0,0,.1);}
        .content .border-box .user-image {position: absolute;top: -50px;left: 50%;overflow: hidden;margin: 0 0 0 -50px;padding: 4px;width: 100px;height: 100px;border: 1px solid #d1d9db;background: #fff;border-radius: 100%;text-align: center;}
        .content .border-box .placeholder-img{width:90px;background-color:#f8fbfd;border-radius:100%}
        .content .border-box .placeholder-img{display:block;height:90px;background-position:50%;background-size:cover;-webkit-animation:fade-in 1s;animation:fade-in 1s}
        .form-group{max-width:550px;margin:15px auto;text-align:left;}
        .form-group.buttons{margin-top:30px;text-align:center;}
        label,input,select{font-size:0.7em;display:inline-block !important;}
        label{width:120px;text-align:right;padding-right:10px;}
        input,select {padding: 3px 8px;width: 300px;border: 1px solid #dbe3e7;color: #677d87;font-weight: 300;-webkit-user-select: text;-moz-user-select: text;-ms-user-select: text;user-select: text;border-radius: 3px;transition: border-color .15s linear;-webkit-appearance: none;}
        #install-form{display:none;}
        .btn {padding: 6px 10px;color:white;outline:0;border-radius:3px;text-shadow:0 -1px 0 rgba(0,0,0,.1); font-size:0.9em;cursor:pointer;}
        .btn-blue{background:-webkit-gradient(linear,left top,left bottom,from(#3ea0d6),to(#2287be));background:linear-gradient(#3ea0d6,#2287be);box-shadow:0 1px 0 rgba(0,0,0,.12)}
        .btn-blue:hover,.btn-blue:active,.btn-blue:focus{background:#1e77a9}
        .btn-green{background:-webkit-gradient(linear,left top,left bottom,from(#97be37),to(#81a133));background:linear-gradient(#97be37,#81a133);box-shadow:0 1px 0 rgba(0,0,0,.12)}
        .btn-green:hover,.btn-green:active,.btn-green:focus{background:#81a133}
        .btn-red{background:-webkit-gradient(linear,left top,left bottom,from(#d74d30),to(#b5391e));background:linear-gradient(#d74d30,#b5391e);box-shadow:0 1px 0 rgba(0,0,0,.12)}
        .btn-red:hover,.btn-red:active,.btn-red:focus{background:#9f321b;}
        .btn:hover{color:white;}
        .div_main{height:600px;width:560px;padding:2px; border:1px solid orange; background-color: #f2c35a;}
        .div_unzip{overflow:auto;height:350px;width:100%;padding:2px;margin:2px;font-size:0.8em;border:1px solid orange;background-color: #ffe0a1;text-align:left;line-height:1em;}
        .div_unzip #progress_info{font-family:Monaco,Consolas,Monospace,'Courier New';-ms-text-size-adjust:100%;-webkit-text-size-adjust: 100%;}
        .div_menu{width:550px;padding:2px;margin:2px;font-size:0.8em;border:1px solid orange;background-color: #ffe0a1}
        .div_info{width:550px;padding:2px;margin:2px;font-size:0.8em;border:1px solid orange;background-color: #ffe0a1}
        .gh-loading-content{display:-webkit-box;-webkit-box-orient:horizontal}
        @-webkit-keyframes throbber-fade{0%,to{opacity:.5}40%,60%{opacity:.8}}
        @keyframes throbber-fade{0%,to{opacity:.5}40%,60%{opacity:.8}}
        @-webkit-keyframes throbber-pulse{0%,to{-webkit-transform:scale3d(.5,.5,1);transform:scale3d(.5,.5,1)}50%{-webkit-transform:scaleX(1);transform:scaleX(1)}}
        @keyframes throbber-pulse{0%,to{-webkit-transform:scale3d(.5,.5,1);transform:scale3d(.5,.5,1)}50%{-webkit-transform:scaleX(1);transform:scaleX(1)}}
        .gh-loading-content{display:flex;flex-direction:row;-webkit-box-align:center;align-items:center;overflow:hidden;top:0;bottom:0;position:absolute;width:100%;-webkit-box-pack:center;justify-content:center;left:0;padding-bottom:8vh}
        .gh-loading-content.basic-auth{z-index:1000}
        .gh-loading-spinner{position:relative;display:inline-block;box-sizing:border-box;margin:-2px 0;width:50px;height:50px;border:1px solid rgba(0,0,0,.1);border-radius:100px;-webkit-animation:spin 1s linear infinite;animation:spin 1s linear infinite}
        .gh-loading-spinner:before{content:"";display:block;margin-top:7px;width:7px;height:7px;background:#4c5156;border-radius:100px;z-index:10}
        @keyframes spin{0%{-webkit-transform:rotate(0);transform:rotate(0)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}
        a{cursor:pointer;}
        #db_okis{display:none;}
        .btn.disabled{cursor:not-allowed;background:#c5cdc5;}
        .btn.disabled:hover{cursor:not-allowed;background:#c5cdc5;}
         #msg_info{font-weight:300;margin-top:5px;font-size:0.8em;text-align:center;line-height:0.9em;border:1px solid #eff1f1; padding: 5px;}
        .msg-error{border:1px solid red;color:#f05230;}
        .msg-info{border:1px solid #61c98d;color:#79cc55;}
        #mysql_data{display:none;}
          #result{display:none;}
         .msg-info,.msg-error{display:block;}
         .information{display:block;border:1px solid #aaa;background-color:#efefef;color:navy;padding:10px;text-align:left;font-size:12px;line-height: 1.1em;}
         pre.code{background-color:#2c466d;max-height:500px;text-align:left;color:#f9f9f9;overflow:hidden;/*width:100%;*/padding:10px 4px 0 4px;border:1px solid gray;line-height:0.9em;font-size:12px;}
         border: 2px solid red;padding: 10px;font-size: 0.8em;line-height: 0.9em;text-align: left;}
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</head>
<body>


<div class="ember-load-indicator" style="display:none;">
    <div class="gh-loading-content">
        <div class="gh-loading-spinner"></div>
    </div>
</div>

<div class="wrap">

    <section class="content">


        <?php


        if (!function_exists('zip_open')){

           echo '<h1 style="color:#790018;">¡No puedor!</h1><p style="color:#ff0033;">Se necesitan las funciones ZIP para PHP.</p>'."<pre class='code'>// Instalar con:\n~ apt install unzip php-zip\n~ /etc/init.d/apache2 restart</pre>";

        } else if (!function_exists('mb_strimwidth')){

           echo '<h1 style="color:#790018;">¡No puedor!</h1><p style="color:#ff0033;">Se necesitan las funciones mb_* para PHP.</p>'."<pre class='code'>// Instalar con:\n~ apt install php-mbstring\n~ /etc/init.d/apache2 restart</pre>";

        } else if (!function_exists('imagecreatefromjpeg')){

           echo '<h1 style="color:#790018;">¡No puedor!</h1><p style="color:#ff0033;">Se necesitan las funciones GD para PHP.</p>'."<pre class='code'>// Instalar con:\n~ apt install php-gd\n~ /etc/init.d/apache2 restart</pre>";

        } else if (strnatcmp(phpversion(),'5.4.0') >= 0){

            if (strnatcmp(phpversion(),'7.2.0') <= 0)
                echo '<p style="color:#ff0033;">Su versión de php es la '.$php_ver.'. Se recomienda la versión 7.2 o superior para activar todas las características.</p>';

                if(file_exists('configuration.php')) include('configuration.php');
                if(file_exists('configuration.mysql.php')) include('configuration.mysql.php');
                $check_mark = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGrSURBVDjLvZPZLkNhFIV75zjvYm7VGFNCqoZUJ+roKUUpjRuqp61Wq0NKDMelGGqOxBSUIBKXWtWGZxAvobr8lWjChRgSF//dv9be+9trCwAI/vIE/26gXmviW5bqnb8yUK028qZjPfoPWEj4Ku5HBspgAz941IXZeze8N1bottSo8BTZviVWrEh546EO03EXpuJOdG63otJbjBKHkEp/Ml6yNYYzpuezWL4s5VMtT8acCMQcb5XL3eJE8VgBlR7BeMGW9Z4yT9y1CeyucuhdTGDxfftaBO7G4L+zg91UocxVmCiy51NpiP3n2treUPujL8xhOjYOzZYsQWANyRYlU4Y9Br6oHd5bDh0bCpSOixJiWx71YY09J5pM/WEbzFcDmHvwwBu2wnikg+lEj4mwBe5bC5h1OUqcwpdC60dxegRmR06TyjCF9G9z+qM2uCJmuMJmaNZaUrCSIi6X+jJIBBYtW5Cge7cd7sgoHDfDaAvKQGAlRZYc6ltJlMxX03UzlaRlBdQrzSCwksLRbOpHUSb7pcsnxCCwngvM2Rm/ugUCi84fycr4l2t8Bb6iqTxSCgNIAAAAAElFTkSuQmCC">';
            ?>

            <section>
                <h1 style="margin:20px auto 0 auto;">Instalación ExtFW <?=$extfw_version?> <a style="font-size:12px;color:<?=$advanced?'red':'silver'?>;" href="install.php<?=$advanced?'':'?mode=advanced'?>"><?=$advanced?'🛠':'🛠'?></a></h1>
                <pre style="text-align:left;line-height:0.75em;overflow:hidden;font-size:0.75em;font-weight:100;">
    Installer: <?=$extfw_installer_version?><br />
        PHP: <?php   echo $php_ver; /*$cfg['prefix']*/?><br />
        ExtFW: <?php   echo $extfw_version; ?><br />
            OS: <?php   echo $php_os; ?><br />
        Server: <?php   echo $server; ?><br />
    Browser: <?php   print_r( $_SERVER['HTTP_USER_AGENT'] ); ?><!--<br />
                OS Info: <?php   print_r( INSTALLER::getOSInformation() ); ?>-->
                </pre>
                <?php
                    if(file_exists('index.php')&&file_exists('configuration.php')){
                        ?><p>El sistema ya está instalado. Debería eliminar este script. <a class="" href="./"> Okis </a></p><?php
                    }else{
                        ?>
                        <p>
                        <!--Once there were brook trout in the streams in the mountains. You could see them standing in the amber current where the white edges of their fins wimpled softly in the flow. They smelled of moss in your hand. Polished and muscular and torsional. On their backs were vermiculate patterns that were maps of the world in its becoming. Maps and mazes. Of a thing which could not be put back. Not be made right again. In the deep glens where they lived all things were older than man and they hummed of mystery.-->
                        </p>
                        <?php
                }
                ?>
                <ol>
                    <?php if($advanced){?>
                        <li id="link-update"><a>Update installer</a> &nbsp; <span id="progress_update">  </span> <span id="progress_update_info">  </span></li>
                        <li id="link-update-extfw"><a>Update ExtFW</a> &nbsp; <span id="progress_update">  </span> <span id="progress_update_info">  </span></li>
                    <?php } ?>
                    <?php

                    if(  file_exists('configuration.php') /*&& file_exists('configuration.mysql.php') */&& file_exists('index.php') ) {
                    }else{
                        ?>
                        <li id="link-download"><a>Download ExtFW <?=$extfw_version?></a> &nbsp; <span id="progress_download">  </span> <span id="progress_download_info">  </span></li>
                        <li id="link-install"  <?=(file_exists($extfw_file.'.zip'))?'':'style="display:none;"' ?>><a>Instalar</a> &nbsp; <span id="progress_install">  </span> <span id="progress_install_info">  </span></li>
                            <form id="install-form" class="border-box">
                                <div class="form-group">
                                    <label for="sitename">Site name</label><input tabindex="1" placeholder="Site name"  type="text" id="sitename" name="sitename"><br />
                                    <label for="email">Email address</label><input tabindex="7" placeholder="Su email"  type="text" id="email" name="email">
                    <?php if($advanced){?>
                                    <label for="prefix">Prefix</label><input tabindex="2" placeholder="Prefix"  type="text" id="prefix" name="prefix" value="<?=$cfg['prefix']?$cfg['prefix']:INSTALLER::makePrefix()?>"><br />
                    <?php } else{ ?>
                                    <input  type="hidden" id="prefix" name="prefix" value="<?=$cfg['prefix']?$cfg['prefix']:INSTALLER::makePrefix()?>">
                    <?php } ?>
                                    <label for="dbtype">Database type</label><select id="dbtype" name="dbtype"><option value="mysql">MySQL</option><option selected value="sqlite">SQLite</option></select><br />
                                    <span id="mysql_data">
                                    <label for="dbname">Db Name</label><input tabindex="4" placeholder="Database name"  type="text" id="dbname" name="dbname" value="<?=$cfg['db']['name']?>"> <span id="db_okis"><?=$check_mark?></span><br />
                                    <label for="dbhost">Db Host</label><input tabindex="4" placeholder="Database host"  type="text" id="dbhost" name="dbhost" value="<?=$cfg['db']['host']?$cfg['db']['host']:'localhost'?>"><br />
                                    <label for="dbuser">Db User</label><input tabindex="5" placeholder="Db username"  type="text" id="dbuser" name="dbuser" value="<?=$cfg['db']['user']?>"><br />
                                    <label for="dbpass">Db Password</label><input tabindex="6" placeholder="Db password"  type="text" id="dbpass"name="dbpass" value="<?=$cfg['db']['pass']?>"><br />
                                    </span>
                                    <div id="msg_info"> .. </div>
                                </div>
                                <div class="form-group buttons">
                                    <a class="btn btn-blue disabled" id="btn-install">Guardar</a>
                                    <a class="btn btn-red">Volver</a>
                                </div>
                            </form>
                        <li id="link-start" style="display:none;"><a href="/install/fresh">Terminar instalación</a></li>
                        <?php
                    }
                    if( file_exists('configuration.php')
                    &&file_exists('configuration.mysql.php')
                    &&file_exists('_modulos_/control_panel/TABLE_'.$cfg['prefix'].'_user.php')
                    &&file_exists('_modulos_/control_panel/TABLE_'.$cfg['prefix'].'_item.php') ){
                        ?><li id="link-refactor"><a>Refactor</a></li><?php
                    }
                    if($advanced){
                    if(file_exists('index.php')&&file_exists('configuration.php')){
                        ?><li id="link-deleteextfw"><a>Eliminar ExtFW</a></li><?php
                    }
                    }
                    ?>
                    <?php if($advanced){?>
                    <li id="link-delete"><a>Eliminar este script <span style="font-size:0.8em;color:#44da12;">Recomendable para evitar accidentes</span></a></li>
                    <li id="link-test" ><a>Test</a> <span id="progress_test"> ... </span></li>
                    <?php } ?>
                    <!---- ---->
                </ol>

                <p>
                <script>
                        var code=-1;
                        var type,host,user,pass,name;

                    //$('#link-deleteextfw').show();
                    //$('#result').hide();

                        $('#link-install a').click(function(){
                            $('#install-form').toggle('fast');
                        });

                        function form_ok(){
                            if( $('#dbtype').val()=='sqlite'  && $('#sitename').val()!=''  && $('#email').val()!='') return true;

                            return  $('#sitename').val() &&
                                    $('#prefix').val() &&
                                    $('#dbtype').val() &&
                                    ( ( $('#dbtype').val()=='mysql' && $('#dbname').val() && $('#dbhost').val() && $('#dbuser').val() && $('#dbpass').val() ) || ($('#dbtype').val()!='mysql') ) &&
                                    $('#email').val() ;
                        }

                        $("#install-form input, #dbtype").on('focus change keyup blur paste', function () {
                            check();
                        });

                        function check() {
                            var needsCheck = false;
                            if ($('#dbtype').val() === 'sqlite') {
                                if (type !== 'sqlite' || code !== 'ok') {
                                    needsCheck = true;
                                }
                            } else if ($('#dbtype').val() === 'mysql') {
                                if (type !== 'mysql' || host !== $('#dbhost').val() || name !== $('#dbname').val() || user !== $('#dbuser').val() || pass !== $('#dbpass').val() || code !== 'ok') {
                                    needsCheck = true;
                                }
                            }

                            if (needsCheck) {
                                code = 'checking'; // Set status to avoid re-triggering
                                $('#btn-install').addClass('disabled');
                                $('#result').hide();
                                $('#msg_info').html('...').removeClass('msg-info msg-error');
                            } else {
                                if (code === 'ok' && form_ok()) {
                                    $('#btn-install').removeClass('disabled');
                                } else {
                                    $('#btn-install').addClass('disabled');
                                }
                                return;
                            }

                            if($('#dbtype').val()=='mysql') $('#mysql_data').fadeIn(); else $('#mysql_data').fadeOut();

                            if (needsCheck) {
                                type = $('#dbtype').val();
                                host = $('#dbhost').val();
                                name = $('#dbname').val();
                                user = $('#dbuser').val();
                                pass = $('#dbpass').val();
                                $('.ember-load-indicator').show();
                                $.ajax({
                                    method: "POST",
                                    url: "install.php",
                                    data: {
                                        'op'  : 'check_db',
                                        'type': type,
                                        'host': host,
                                        'name': name,
                                        'user': user,
                                        'pass': pass
                                    },
                                    dataType: "json"
                                }).done(function( data ) {
                                    if(data.ok === 'ok') {
                                        code = 'ok';
                                        $('#db_okis').show();
                                        if ($('#sitename').val() !== ''  && $('#email').val() !== '') {
                                            $('#btn-install').removeClass('disabled');
                                            $('#msg_info').addClass('msg-info').html('Conexión con la base de datos OK. Puede continuar.');
                                        } else {
                                            $('#msg_info').addClass('msg-info').html('Conexión con la base de datos OK. Rellene los campos para continuar.');
                                        }
                                    } else {
                                        code = 'ko';
                                        $('#db_okis').hide();
                                        $('#btn-install').addClass('disabled');
                                        $('#msg_info').addClass('msg-error').html('Error ' + (data.code || '') + ': ' + (data.msg || 'Error desconocido'));
                                        if(data.sql) $('#msg_info').append('<pre class="code">' + data.sql + '</pre>');
                                    }
                                }).fail(function() {
                                    $('#btn-install').addClass('disabled');
                                    $('#msg_info').addClass('msg-error').html('Error de comunicación con el servidor.');
                                }).always(function() {
                                    $('.ember-load-indicator').hide();
                                });
                            }
                        }

                        setTimeout( function(){ check() }, 500 );

                        $('#btn-install').click(function(e){
                            if($(this).hasClass('disabled')){
                                e.preventDefault();
                                $('#msg_info').addClass('msg-error').html('Por favor, rellene todos los campos requeridos.');
                                return false;
                            }

                            var n = 0,m = 0,r = '',points = '';
                            var p = $('#progress_install');
                            var interval_install = setInterval(function(){
                                    n++; m++;
                                    if(m>1) m=0;
                                    if(m==1) points = points + '· ';
                                    switch(n) {
                                        case 0:  r = '—'; break;
                                        case 1:  r = '\\'; break;
                                        case 2:  r = '|'; break;
                                        case 3:  r = '/'; n= -1; break;
                                        }
                                        p.html( points + r );
                                }, 100);

                            $('.ember-load-indicator').show();
                            $.ajax({
                            method: "POST",
                            url: "install.php",
                            data: {
                                op    : 'install',
                                sitename: $('#sitename').val(),
                                prefix: $('#prefix').val(),
                                dbtype: $('#dbtype').val(),
                                dbname: $('#dbname').val(),
                                dbhost: $('#dbhost').val(),
                                dbuser: $('#dbuser').val(),
                                dbpass: $('#dbpass').val(),
                                email:  $('#email').val()
                            },
                            dataType: "json"
                            }).done(function( data ) {
                                if(data.error==1){
                                    $('#msg_info').addClass('msg-error').html(data.msg);
                                }else{
                                    $('#progress_install_info').html('<?=$check_mark?>');
                                    $('#install-form').hide('fast');
                                    $('#link-start').show('fast');
                                    $('#link-delete').show('fast');
                                }
                            }).fail(function(data) {
                                $('#msg_info').addClass('msg-error').html('Error en la instalación.');
                            }).always(function() {
                                clearInterval(interval_install);
                                p.html( points+' ');
                                $('.ember-load-indicator').hide();
                            });
                        });

                        $('#link-update').click(function(){     // op: 'update', host: '<?=$host_extfw?>', file: '<?=$extfw_installer?>'
                            var n = 0,m = 0,r = '',points = '';
                            var p = $('#progress_update');
                            var interval_update = setInterval(function(){
                                    n++; m++;
                                    if(m>1) m=0;
                                    if(m==1) points = points + '· ';
                                    switch(n) {
                                        case 0:  r = '—'; break;
                                        case 1:  r = '\\'; break;
                                        case 2:  r = '|'; break;
                                        case 3:  r = '/'; n= -1; break;
                                        }
                                        p.html( points + r );
                                }, 100);
                            //$('#link-install,#link-cfg').hide('fast');
                            $('.ember-load-indicator').show();
                            $.ajax({
                            method: "POST",
                            url: "install.php", //?op=download",
                            data: { op: 'update', host: '<?=$host_extfw?>', file: '<?=$extfw_installer?>' },
                            dataType: "json",
                            beforeSend: function( xhr, settings ) {
                                //$('#progress_info').html('descarganding ...');
                            }
                            }).done(function( data ) {
                            $('#progress_update_info').html('<?=$check_mark?>');
                            //$('#link-update').hide('fast');
                            console.log('DONE');
                            location.href="install.php";
                        }).fail(function() {
                            console.log('ERROR');
                            }).always(function() {
                            clearInterval(interval_update);
                            p.html( points+' ');
                            console.log('COMPLETE');
                            $('.ember-load-indicator').hide();
                            });
                        });

                        $('#link-update-extfw').click(function(){     // op: 'update', host: '<?=$host_extfw?>', file: '<?=$extfw_update?>'
                            var n = 0,m = 0,r = '',points = '';
                            var p = $('#progress_update');
                            var interval_update = setInterval(function(){
                                    n++; m++;
                                    if(m>1) m=0;
                                    if(m==1) points = points + '· ';
                                    switch(n) {
                                        case 0:  r = '—'; break;
                                        case 1:  r = '\\'; break;
                                        case 2:  r = '|'; break;
                                        case 3:  r = '/'; n= -1; break;
                                        }
                                        p.html( points + r );
                                }, 100);
                            //$('#link-install,#link-cfg').hide('fast');
                            $('.ember-load-indicator').show();
                            $.ajax({
                            method: "POST",
                            url: "install.php", //?op=download",
                            data: { op: 'update', host: '<?=$host_extfw?>', file: '<?=$extfw_update?>' },
                            dataType: "json",
                            beforeSend: function( xhr, settings ) {
                                //$('#progress_info').html('descarganding ...');
                            }
                            }).done(function( data ) {
                            $('#progress_update_info').html('<?=$check_mark?>');
                            //$('#link-update').hide('fast');
                            console.log('DONE');
                            location.href="install.php";
                        }).fail(function() {
                            console.log('ERROR');
                            }).always(function() {
                            clearInterval(interval_update);
                            p.html( points+' ');
                            console.log('COMPLETE');
                            $('.ember-load-indicator').hide();
                            });
                        });

                        $('#link-download').click(function(event){  // op: 'download', host: '<?=$host_extfw?>', file: '<?=$extfw_file?>'
                            event.preventDefault();
                            var n = 0,m = 0,r = '',points = '';
                            var p = $('#progress_download');
                            var interval_download = setInterval(function(){
                                    n++; m++;
                                    if(m>1) m=0;
                                    if(m==1) points = points + '· ';
                                    switch(n) {
                                        case 0:  r = '—'; break;
                                        case 1:  r = '\\'; break;
                                        case 2:  r = '|'; break;
                                        case 3:  r = '/'; n= -1; break;
                                        }
                                        p.html( points + r );
                                }, 100);
                            $('#link-install,#link-cfg').hide('fast');
                            $('.ember-load-indicator').show();
                            $.ajax({
                            method: "POST",
                            url: "install.php", //?op=download",
                            data: { op: 'download', host: '<?=$host_extfw?>', file: '<?=$extfw_file?>' },
                            dataType: "json",
                            beforeSend: function( xhr, settings ) {
                                //$('#progress_info').html('descarganding ...');
                            }
                            }).done(function( data ) {
                            if(data.error>0){
                                $('#result').removeClass('information').html('<b>ERROR</b> '+data.msg).removeClass('msg-info').addClass('msg-error');
                                $('#progress_download_info').html(' :( ');
                            }else{
                                $('#progress_download_info').html('<?=$check_mark?>');
                                $('#link-install,#link-cfg').show('fast');
                            }
                            console.log('DONE');
                            }).fail(function(status) {
                            console.log('ERROR',status.responseText);
                            $('#result').removeClass('information').html(data.msg).removeClass('msg-info').addClass('msg-error');
                            }).always(function() {
                            clearInterval(interval_download);
                            p.html( points+' ');
                            console.log('COMPLETE');
                            $('.ember-load-indicator').hide();
                            });
                        });

                        $('#link-delete').click(function(){
                            $('.ember-load-indicator').show();
                            $.ajax({
                            method: "POST",
                            url: "install.php", //?op=download",
                            data: { op: 'delete'},
                            dataType: "json",
                            beforeSend: function( xhr, settings ) { }
                            }).done(function( data ) {
                            console.log('DONE');
                            }).fail(function() {
                            console.log('ERROR');
                            }).always(function(data) {
                            $('#result').removeClass('information').html(data.msg);
                            console.log('COMPLETE');
                            $('.ember-load-indicator').hide();
                            });
                        });

                        $('#link-refactor').click(function(){
                            $.ajax({
                            method: "POST",
                            url: "install.php", //?op=download",
                            data: { op: 'refactor'},
                            dataType: "json",
                            beforeSend: function( xhr, settings ) { }
                            }).done(function( data ) {
                            console.log('DONE',data);
                            }).fail(function() {
                            console.log('ERROR');
                            }).always(function(data) {
                            $('#result').removeClass('information').html(data.msg);
                            console.log('COMPLETE');
                            });
                        });

                        $('#link-deleteextfw').click(function(){
                            let okis = prompt('¿Esta usted seguro? Si es que sí debe escribir la contraseña de borrado.');
                            if (okis=='yes') {
                                alert('Tu lo has querido :(');
                                /**/
                                $('.ember-load-indicator').show();
                                $.ajax({
                                method: "POST",
                                url: "install.php", //?op=download",
                                data: { op: 'deleteextfw'},
                                dataType: "json",
                                beforeSend: function( xhr, settings ) { }
                                }).done(function( data ) {
                                console.log('DONE',data);
                                }).fail(function() {
                                console.log('ERROR');
                                }).always(function(data) {
                                $('#result').removeClass('information').html(data.msg).show();
                                console.log('COMPLETE');
                                $('.ember-load-indicator').hide();
                            });
                                /**/
                            }else{
                                alert('Bien pensado :)');
                            }
                        });

                        $('#link-test').click(function(){
                            $('.ember-load-indicator').show();
                            $.ajax({
                            method: "POST",
                            url: "install.php", //?op=download",
                            data: { op: 'test'},
                            dataType: "json",
                            beforeSend: function( xhr, settings ) { }
                            }).done(function( data ) {
                            console.log('DONE');
                            console.log(data.msg);
                            }).fail(function(data) {
                            $('#result').removeClass('information').html('<b>ERROR</b> '+data.msg).removeClass('msg-info').addClass('msg-error');
                            console.log('ERROR');
                            }).always(function(data) {
                            $('#result').removeClass('information').html(data.msg);
                            console.log('COMPLETE');
                            $('.ember-load-indicator').hide();
                            });
                        });
                </script>
                </p>
                <div id="result">
                </div>
            </section>
            <?php
        }else{
            echo '<h1 style="color:#790018;">¡No puedor!</h1><p style="color:#ff0033;">Su versión de php es la '.$php_ver.'. Se necesita como mínimo la versión 5.4 para un funcionamiento básico. Y la 7.2 o superior para activar todas las características.</p>';
        }
        ?>
        <div class="msg-error" style="display:none;">acho si</div>
    </section>
</div>
<footer>
    2020 &copy;extralab · Todos los berberechos reservados
</footer>
</body>
</html>