<?php


//  https://stackoverflow.com/questions/4705759/how-to-get-cpu-usage-and-ram-usage-without-exec/38085813#38085813
//  text https://phpsysinfo.github.io/phpsysinfo/

class SYS{
   
    private static function formatBytes($b){
        if      ($b > 1024*1024) return number_format($b/1024/1024,2).'Gb';
        else if ($b > 1024)      return number_format($b/1024     ,2).'Mb';
        else                     return number_format($b          ,2).'Kb';
    }

    public static function Info(){
       if(function_exists('shell_exec')) {
        $exec_loads = sys_getloadavg();
        $exec_cores = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
        $cpu = $exec_loads[0]*100; //round($exec_loads[1]/($exec_cores + 1)*100, 0); // . '%';

        $exec_free = explode("\n", trim(shell_exec('free')));
        $get_ram = preg_split("/[\s]+/", $exec_free[1]);
        $ram = round($get_ram[2]/$get_ram[1]*100, 0); // . '%';

        $exec_free = explode("\n", trim(shell_exec('free')));
        $get_mem = preg_split("/[\s]+/", $exec_free[1]);
        $used_mem  = self::formatBytes($get_mem[2]);
        $total_mem = self::formatBytes($get_mem[1]);

        $exec_uptime = preg_split("/[\s]+/", trim(shell_exec('uptime')));
        $upt = $exec_uptime[2] . ' Days';
        return [
                 'cpu'=>$cpu
                ,'ram'=>$ram
                ,'used_mem'=>$used_mem
                ,'total_mem'=>$total_mem
                //,'uptime'=>$upt
               ];
        }else{
        return [
                 'cpu'=>'1'
                ,'ram'=>'1'
                ,'used_mem'=>'1'
                ,'total_mem'=>'1'
                //,'uptime'=>$upt
               ];
        }

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
    /*
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
    **/

    public static function mkdirr($dir, $mode = 0755, $recursive = true) {
          if( is_null($dir) || $dir === "" ) return FALSE;
          if( is_dir($dir) || $dir === "/" ) return TRUE;
          if( self::mkdirr(dirname($dir), $mode, $recursive) ) return mkdir($dir, $mode);
          return FALSE;
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

    /*
    public static function rmdirr($dir) { 
       if (is_dir($dir)) { 
         $objects = scandir($dir);
         foreach ($objects as $object) { 
           if ($object != "." && $object != "..") { 
             if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
               self:.rmdirr($dir. DIRECTORY_SEPARATOR .$object);
             else
               unlink($dir. DIRECTORY_SEPARATOR .$object); 
           } 
         }
         rmdir($dir); 
       } 
    }
    */

    public static function get_mime_type($idx) {
        $idx = strtolower($idx);

        $mimet = array( 
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            '7z' => 'application/zip',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset( $mimet[$idx] )) {
         return $mimet[$idx];
        } else {
         return 'application/octet-stream';
        }
    }

    public static function addFileToZip($zipfile,$from,$to){
        $zip = new ZipArchive;
        if ($zip->open($zipfile) === TRUE) {
            $zip->addFile($from, $to);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    public static function unzip($zipfile, $to='./'){
        $zip = new ZipArchive;
        $res = $zip->open($zipfile);
        if ($res === TRUE) {
          $zip->extractTo($to);
          $zip->close();
          return true;
        } else {
          return false;
        }
    }
    /*
    public static function file_viewers(){

        include_once(SCRIPT_DIR_LIB.'/file_viewer/pdf_viewer.php'); 
        include_once(SCRIPT_DIR_LIB.'/file_viewer/epub_viewer.php'); 
        include_once(SCRIPT_DIR_LIB.'/file_viewer/txt_viewer.php'); 
        include_once(SCRIPT_DIR_LIB.'/file_viewer/url_viewer.php'); 
        include_once(SCRIPT_DIR_LIB.'/file_viewer/json_viewer.php');

    }
    */

    public static function create_file($filename,$contenido=false){
        //if(!$contenido) $contenido = '/** '.$filename.' **/';
        if (file_exists($filename)){
            Messages::error("El archivo: <i>$filename</i> ya existe!");
            return false;
        }else {
            if($hfp = fopen($filename,'w+')){
                fwrite($hfp,$contenido);
                Messages::info("Archivo <i>$filename</i> creado!");
                fclose($hfp);
                return true;
            }else{ 
                Messages::error("No ha sido posible crear el archivo: <i>$filename</i>!");
                return false;
            }
        }
    } 

    public static function create_file_php($filename,$contenido=''){
        $contenido = '<'."?php\n".stripslashes($contenido)."\n";
        return self::create_file($filename,$contenido);
    }
      
    public static function create_file_html($filename,$contenido=''){
        $contenido = "<html>\n"
                   ."\t<head>\n\t</head>\n"
                   ."\t<body>".$contenido."\n\t</body>\n"
                   ."</html>";
        return self::create_file($filename,$contenido);
    } 

    public static function create_file_css($filename,$contenido=''){
        $contenido = "/*\n".stripslashes($contenido)."\n*/";
        return self::create_file($filename,$contenido);
    }
                  
    public static function create_file_js($filename,$contenido=''){
        $contenido = 'document.addEventListener("DOMContentLoaded", function(){'."\n\n"
                   .   $contenido."\n\n"
                   .'});';
        return self::create_file($filename,$contenido);
    } 

   
}
