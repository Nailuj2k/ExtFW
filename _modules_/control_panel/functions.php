<?php

/*********
//https://github.com/matthiasmullie/minify
require_once SCRIPT_DIR_LIB . '/minify/Minify.php';
require_once SCRIPT_DIR_LIB . '/minify/CSS.php';
//require_once SCRIPT_DIR_LIB . '/minify/JS.php';
require_once SCRIPT_DIR_LIB . '/minify/Exception.php';
//require_once SCRIPT_DIR_LIB . '/minify/Exceptions/BasicException.php';
//require_once SCRIPT_DIR_LIB . '/minify/Exceptions/FileImportException.php';
//require_once SCRIPT_DIR_LIB . '/minify/Exceptions/IOException.php';
require_once SCRIPT_DIR_LIB . '/path-converter/ConverterInterface.php';
require_once SCRIPT_DIR_LIB . '/path-converter/Converter.php';
// https://github.com/mrclay/jsmin-php
require_once SCRIPT_DIR_LIB . '/jsmin/JSMin.php';
require_once SCRIPT_DIR_LIB . '/jsmin/UnterminatedCommentException.php';
require_once SCRIPT_DIR_LIB . '/jsmin/UnterminatedRegExpException.php';
require_once SCRIPT_DIR_LIB . '/jsmin/UnterminatedStringException.php';

require_once SCRIPT_DIR_LIB . '/tiny-html-minifier/TinyMinify.php';
**/
/*****************************************************
if(file_exists(SCRIPT_DIR_LIB . '/phpwee-php-minifier/phpwee.php'))
//                if (CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password']){
require_once SCRIPT_DIR_LIB . '/phpwee-php-minifier/phpwee.php';
***************************************/
///use MatthiasMullie\Minify;

function createZipFile($verbose=false){
  global $zipfilename;
  $zipfilehandle = new zipfile();
  if ($verbose) echo "zipfile created OK<br />";
  return $zipfilehandle;
}

function addFileToZip($zipfilehandle,$from,$to,$verbose=false){
  if (file_exists($from)) {
    if ($verbose===true) echo  "Adding FILE $from as $to to zip<br />";
    $f_tmp = @fopen( $from , 'r');
    if($f_tmp) {
      $dump_buffer=fread( $f_tmp, filesize($from));
      $zipfilehandle -> addFile($dump_buffer, $to);
      fclose( $f_tmp );
    }
  }
}
/*****
function minifyPHP($phpcode){
       $string = php_strip_whitespace($phpcode);
        if ($this->getBanner()) {
            $string = preg_replace('/^<\?php/', '<?php ' . $this->getBanner(), $string);
        }
        return $string;
}
*/
/***************
function addToZip($zipfilehandle,$ad_dir,$verbose=false){  
  if (!is_dir($ad_dir)) {
    if ($verbose) echo  "Adding FILE $ad_dir to zip<br />";
    $f_tmp = @fopen( $ad_dir , 'r');
    if($f_tmp) {
      $dump_buffer=fread( $f_tmp, filesize($ad_dir));
      $zipfilehandle -> addFile($dump_buffer, $ad_dir);
      fclose( $f_tmp );
    }
  }else{
    if ($handle = opendir($ad_dir)) {
      while (false !== ($file = readdir($handle)))  {
        if ($file == "." || $file == ".." ) {
          if ($verbose) echo  "ignoring $ad_dir/$file <br />";
        } else if (is_dir($ad_dir. '/' .$file)) {
          $messages['msg'][] =  "$ad_dir/$file is a directory<br />";
          addToZip($zipfilehandle,$ad_dir. '/' . $file,false);
        }else  {
          $messages['msg'][] =  "Adding file $ad_dir/$file ..";
          $f_tmp = @fopen( $ad_dir . '/' . $file, 'r');
          if($f_tmp) {
            if (filesize($ad_dir . '/' . $file)) $dump_buffer=fread( $f_tmp, filesize($ad_dir . '/' . $file));
                                            else $dump_buffer='';
            $zipfilehandle -> addFile($dump_buffer, $ad_dir . '/' . $file);
            fclose( $f_tmp );
          }
          if ($verbose) echo ". OK<br />";
        }
      }  
    }
  }  
}
****/

function compress_php_code($content, $delStart = true){
    $content = preg_replace('/[\\s]*\\?\\>$/', '', trim($content));
    $delStart && ($content = preg_replace('/^\\<\\?php[\\s]*/', '', $content));
    return $content;
}

function addToZip($zipfilehandle,$ad_dir,$verbose=false,$compress=false){
 // $verbose=true;
  if (!is_dir($ad_dir)) {
    //if ($verbose) echo  "Adding FILE $ad_dir to zip<br />";
    $f_tmp = @fopen( $ad_dir , 'r');
    $ext = Str::get_file_extension($ad_dir);
    //if ($ext=='jpg') {
      if($f_tmp) {
        if ($compress && $ext=='css') {
            if ($verbose) echo  "minify CSS $ad_dir <br />";
            //$dump_buffer=fread( $f_tmp, filesize($ad_dir));
            //$minifier = new Minify\CSS($dump_buffer);
            //$dump_buffer = $minifier->minify();
            //          $dump_buffer = PHPWee\Minify::css(file_get_contents($ad_dir));
        }else if ($compress && $ext=='js') {
            if ($verbose) echo  "minify JS $ad_dir <br />";
            //$dump_buffer=fread( $f_tmp, filesize($ad_dir));
            //$dump_buffer = \JSMin\JSMin::minify($dump_buffer);
            //          $dump_buffer = PHPWee\Minify::js(file_get_contents($ad_dir));
        }else if (/*1==2 && */$compress && $ext=='php') {
            if ($verbose) echo  "minify PHP $ad_dir <br />";
            $dump_buffer = preg_replace('/^<\?php/', '<?php ' . '/* (c) ExtFW 3.0.1 */', php_strip_whitespace($ad_dir));
        }else{

            $_l_ = filesize($ad_dir);
            if($_l_ < 1){
                $_l_=1;
                $dump_buffer = ' ';
            }

            //if ($ext=='jpg') 

            //print_r($ad_dir.'<br>');
            $dump_buffer=fread( $f_tmp, $_l_);
        }

        //echo 'FILE '.$ad_dir .'<br>';
        $zipfilehandle -> addFile($dump_buffer, $ad_dir);
        fclose( $f_tmp );
      }
    //}  // if ($ext=='jpg') {
  }else{
    if ($handle = opendir($ad_dir)) {
      while (false !== ($file = readdir($handle)))  {
        if ($file == "." || $file == ".." || $file == "log" ) {
          //if ($verbose) echo  "ignoring $ad_dir/$file <br />";
        } else if (strpos($file,'_js_')!==false && (strpos($file,'.0')!==false||strpos($file,'.test')!==false||/*strpos($file,'.ok')!==false||*/strpos($file,'.bak')!==false||strpos($file,'.zip')!==false) ){
          //if ($verbose) echo  "ignoring $ad_dir/$file <br />";
        } else if (is_dir($ad_dir. '/' .$file)) {
          $messages['msg'][] =  "$ad_dir/$file is a directory<br />";
          addToZip($zipfilehandle,$ad_dir. '/' . $file,$verbose,$compress);
        }else  {
          $messages['msg'][] =  "Adding file $ad_dir/$file ..";
          $f_tmp = @fopen( $ad_dir . '/' . $file, 'r');
          if($f_tmp) {
              $ext = Str::get_file_extension($file);

              //if ($ext=='jpg') {

                if (filesize($ad_dir . '/' . $file)) {
                    if ($compress && $ext=='css') {
                        if ($verbose) echo  "minify CSS $ad_dir/$file <br />";
                        //$dump_buffer=fread( $f_tmp, filesize($ad_dir . '/' . $file));
                        //$minifier = new Minify\CSS($dump_buffer);
                        //$dump_buffer = $minifier->minify();
                        //                      $dump_buffer = PHPWee\Minify::css(file_get_contents($ad_dir . '/' . $file));
                    }else if ($compress && $ext=='js') {
                        if ($verbose) echo  "minify JS $ad_dir/$file <br />";
                        //$dump_buffer=fread( $f_tmp, filesize($ad_dir . '/' . $file));
                        //$dump_buffer = \JSMin\JSMin::minify($dump_buffer);
                        //                      $dump_buffer = PHPWee\Minify::js(file_get_contents($ad_dir . '/' . $file));
                    }else if (/*1==2 &&*/ $compress && $ext=='php') {
                        //  https://php-minify.com/php-obfuscator/
                        // https://www.php.net/manual/en/function.php-strip-whitespace.php
                        if ($verbose) echo  "minify PHP $ad_dir/$file <br />";
                        //if ($file=='index.php'||$file=='index.header.php'||$file=='footer.php'||$file=='run.php') {
                        //    $dump_buffer = compress_php_code(php_strip_whitespace($ad_dir . '/' . $file)) ;      
                        //  //$dump_buffer=str_replace(['  ',"\n"],[' ',' '],$dump_buffer);
                        //    $dump_buffer=str_replace('  ',' ',$dump_buffer);
                        //}else{
                            $dump_buffer = preg_replace('/^<\?php/', '<?php ' . '/* (c) ExtFW 3.0.1 */', php_strip_whitespace($ad_dir . '/' . $file) );
                        //}

                    }else{
                        $dump_buffer=fread( $f_tmp, filesize($ad_dir . '/' . $file));
                    }
                }else {
                    $dump_buffer='';
                }
                //echo 'FILE '.$ad_dir . '/' . $file.'<br>';
                $zipfilehandle -> addFile($dump_buffer, $ad_dir . '/' . $file);
                fclose( $f_tmp );
              //}  // if ($ext=='jpg') {

          }
          //if ($verbose) echo ". OK<br />";
        }
      }  
    }
  }  
}


function saveZipFile($zipfilehandle,$zipfilename,$verbose=false){
  $dump_buffer = $zipfilehandle -> file();
  // write the file to disk:
  $file_pointer = fopen($zipfilename, 'w');
  if($file_pointer){
    fwrite( $file_pointer, $dump_buffer, strlen($dump_buffer) );
    fclose( $file_pointer );
    if ($verbose) echo "$zipfilename saved OK<br />";
  } else {
    if ($verbose) echo "ERROR: $zipfilename not saved<br />";
  }
}

function download ($file_source, $file_target, $verbose=false){
    $messages = array();
    $messages['error'] =  0;
    $messages['msg'][] =  " Downloading $file_source <br>";  

    $stream_opts = [
        "ssl" => [
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ]
    ];  
   
    $file_source = str_replace(' ', '%20', html_entity_decode($file_source)); // fix url format

    if (file_exists($file_target)) { 

        chmod($file_target, 0777); 

    } // add write permission

    $response = file_get_contents($file_source,     false, stream_context_create($stream_opts));
    file_put_contents($file_target, $response);

    /*
    if (($rh = fopen($file_source, 'rb')) === false) { 
        $messages['msg'][] = "FAIL ( fopen() error )";
        $messages['error'] =  1; 
    } 

    if (($wh = @fopen($file_target, 'wb')) === FALSE) { 
        $messages['msg'][] = "FAIL (Unknown error)";
        $messages['error'] =  1; 
    } 
    if ($verbose) {
        $num_points = 0;
        $num_steps = 0;
        $steps = 10;
    }
    while (!feof($rh)) {
        if (fwrite($wh, fread($rh, 8192)) === FALSE) { 
            $messages['msg'][] =  "FAIL (Unable to write output)";
            $messages['error'] =  1; 
            fclose($rh); 
            fclose($wh); 
           return false; 
        }
        if ($verbose) {
          usleep(5000);
          $num_steps++;
          if($num_steps>=$steps){$num_steps=0;$num_points++;echo '.';}
          if($num_points>=100){$num_points=0;echo '<br />';}
          ob_flush();
          flush();
        }     
    }
    fclose($rh);
    fclose($wh);
    */

    if (file_exists($file_target) && filesize($file_target)>10) {
        $messages['msg'][] =  "OK";
    }else{
        $messages['msg'][] = "FAIL (Unknown error)".filesize($file_target);
        $messages['error'] =  1; 
    }
    return $messages;
}

/* * *
 * Delete a file, or a folder and its contents (recursive algorithm)
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.3
 * @link        http://aidanlister.com/repos/v/function.rmdirr.php
 * @param       string   $dirname    Directory to delete
 * @return      bool     Returns TRUE on success, FALSE on failure
 */

function rmdirr($dirname,$verbose=false){

    // Sanity check
    if (!file_exists($dirname)) {  if ($verbose) echo " Directory $dirname does not exists<br>";  return false; }
    // Simple delete for a file

    if (is_file($dirname) || is_link($dirname)) { 
      if ($verbose) echo " Deleting $dirname as file<br />"; 
      return unlink($dirname); 
    }
    // Loop through the folder

    if ($verbose) echo "deleting: $dirname";

    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') { continue; }
        // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }
    // Clean up
    $dir->close();
    if ($verbose) echo " OK<br>";
    return rmdir($dirname);
}


function rmfiles($dirname){

    // Sanity check
    if (!file_exists($dirname)) {  if ($verbose) echo " Directory $dirname does not exists<br>";  return false; }

    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') { continue; }
        if (is_file($dirname . DIRECTORY_SEPARATOR . $entry))  unlink($dirname . DIRECTORY_SEPARATOR . $entry);
    }
    $dir->close();
    if ($verbose) echo " OK<br>";
}

function movefiles($source,$dest,$ext=''){

    // Sanity check
    if (!file_exists($source)) {
      if ($verbose) echo " Directory $dirname does not exists<br>";  
      return false; 
    }
    else echo "Moviendo ...<br />";

    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
      // Skip pointers
      if ($entry == '.' || $entry == '..') { continue; }
      if (is_file($source . DIRECTORY_SEPARATOR . $entry)) {
        echo "Moviendo $source.DIRECTORY_SEPARATOR.$entry a $dest.DIRECTORY_SEPARATOR.$entry"; 
        $ee = strtolower(strrchr($entry,'.'));
        $ok = ((($ext=='images') && (($ee=='.jpg')||($ee=='.png')||($ee=='.gif'))) || ($ext=='files')) ;
        if($ok){ 
          if(copy($source . DIRECTORY_SEPARATOR . $entry, $dest . DIRECTORY_SEPARATOR . $entry)){
            unlink($source . DIRECTORY_SEPARATOR . $entry);
            echo " OK<br />";
          } else  echo " FAIL<br />";
        } else  echo " $ee no file!<br />";
      }
    }
    $dir->close();
}


function testrmdir( ){
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
  $status = rmdirr('testdelete');

  // Check if we passed the test
  if ($status === true &&
        !file_exists('testdelete') &&
        file_exists('testlink/testfile')) {
    echo 'TEST PASSED';
    rmdirr('testlink');
  } else {
    echo 'TEST FAILED';  
  }
}

/* * *
 * 
    https://www.php.net/manual/en/ziparchive.open.php
    To read a zip file contents then loop through $zip->numFiles using $zip->getNameIndex(i)
    
            $zipfilename=$dir.DIRECTORY_SEPARATOR."test.zip";
            $zippedFile = new ZipArchive;
            $zippedFile->open($zipfilename);

            echo "<LI>".$zippedFile->getFromName("mpdf-8.0.7/.gitignore")."</LI><HR>";

            echo "<LI>Loaded $zipfilename ".$zippedFile->numFiles;
            for($i = 0; $i < $zippedFile->numFiles; $i++) {
                $fn=$zippedFile->getNameIndex($i);
                echo "<LI>$i: ".$fn;

                if ( strcmp(substr($fn, -1), DIRECTORY_SEPARATOR )==0 ) echo "... directory";
                else echo "... ".strlen($zippedFile->getFromIndex($i))." bytes";

            }
            $zippedFile->close();

 * 
 * 
 */


 function unzip($dir,$file, $verbose = false,$to='./'){
    //$verbose = true;
      $messages = array();
      $messages['error'] =  0;
      $messages['msg'] =  " Extracting $file <br>";  
      if ($zip = zip_open($dir.$file.".zip")) {
        if ($zip) {
          //mkdir($dir.$file);
          while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip,$zip_entry,"r")) {
              $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
              if(!$buf) $buf = '';
              $zip_entry_name = zip_entry_name($zip_entry);
              $dir_name = dirname($zip_entry_name);
              if ($dir_name != ".") {
                $dir_op = $to; //$dir.$file."/";
                foreach ( explode("/",$dir_name) as $k) {
                  $dir_op = $dir_op . $k;
                  if (is_file($dir_op)) unlink($dir_op);
                  if (!is_dir($dir_op)) mkdir($dir_op);
                  $dir_op = $dir_op . "/" ;
                }
              }
              if ($verbose) echo "extracting: ".$zip_entry_name;
              if (!file_exists($to.$zip_entry_name) || is_writable($to.$zip_entry_name)) {
                  $fp=fopen($to.$zip_entry_name,"w");
                  $oki = $fp === false ? false : fwrite($fp,$buf);
                  if ($verbose) echo " ... ".($oki!==false?'OK':'FAIL')."<br>";
                  zip_entry_close($zip_entry);
                  if($oki===false) {
                      $messages['error'] =  1; 
                      $messages['msg']= "Extracting ".$zip_entry_name.' FAIL';
                      //break;  
                  }
              }else{
                  $messages['msg'] = "FAIL file not writable: $to/".$zip_entry_name;
                  $messages['error'] =  1; 
                  //if (chmod("./".$zip_entry_name, 0666)) {
                  //if (chown("./".$zip_entry_name, 'www-data'))  $messages['msg'] = "FAIL file not writable: ./".$zip_entry_name." Corrected";
                  break;
              }
            } else {
                $messages['msg'] = "FAIL cant read zip fileentry: $to/".$dir.$file.".zip";
                $messages['error'] =  1; 
                break;
            }
          }
          zip_close($zip);
        }
      } else {
         $messages['msg'] = "FAIL cant open zip file $to/".$dir.$file.".zip";
         $messages['error'] =  1; 
      }
      if ($verbose) Vars::debug_var($messages);
      return $messages;
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/repos/v/function.copyr.php
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */

function copyr($source, $dest, $verbose=false, $ext=''){

    if (!file_exists($source)) { if ($verbose) echo "Directory $source does not exists<br>"; return false;  }

    if ($verbose) echo "copying: $source to $dest";

    // Check for symlinks
    if (is_link($source)) { if ($verbose) echo " OK<br>"; return symlink(readlink($source), $dest); }
    // Simple copy for a file
    if (is_file($source)) { 
    
      if($ext=='images'){
        $ee = strtolower(strrchr($source,'.'));
        if (($ee=='.jpg')||($ee=='.png')||($ee=='.gif')) return @copy($source, $dest); 
      }
      else{ 
        if ($verbose) echo " OK<br>"; 
        return @copy($source, $dest);  
      }
      
    }
    // Make destination directory
    if (!is_dir($dest))  { @mkdir($dest);  }
   
    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {  continue;  }
        // Deep copy directories
        $ok = copyr("$source/$entry", "$dest/$entry");
        if (!$ok) {if ($verbose) echo " FAIL<br/>";return false;}
    }
    // Clean up
    $dir->close();
    if ($verbose) echo " OK<br>";
    
    return true;
    
}

function mover($source, $dest, $verbose=false){
  if (copyr($source, $dest, $verbose))
    rmdirr($source,$verbose);
}

function mkdirs($dir, $verbose=false){
  if (!file_exists($dir)) { 
    mkdir($dir); 
    if ($verbose) echo " mkdir $dir OK<br>";
  }
  else{
   if ($verbose) echo "dir $dir already exists<br />"; 
  }
}

function testcopydir(){
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

  $status = copyr('testcopy', 'testcopy-copy');

  if (file_exists('testcopy-copy')
        && file_exists('testcopy-copy/one-b/.hiddenfile')
        && file_exists('testcopy-copy/one-c/two-a/testfile')
        && is_link('testcopy-copy/one-d/my-symlink-relative')
        && (readlink('testcopy-copy/one-d/my-symlink-relative') == '../')
        && is_link('testcopy-copy/one-d/my-symlink')) {
    echo "TEST PASSED";
  } else {
    echo "TEST FAILED";
  }

}

function escape($value) {                                 //ADD 20140423
      //  if($this->driver=='mysql') return mysql_real_escape_string($value);   //ADD 20140423
      //                        else return $value;                             //ADD 20140423
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
        return str_replace($search, $replace, $value);
} 


function str2type($str_type){
      $af = explode(  '(',  str_replace(array(')',' ','unsigned'), '', $str_type)  );
      $type=$af[0];
      return ($type == 'int' || $type == 'smallint' || $type == 'hidden' || $type == 'tinyint') ? 'int' : $str_type;
}

function backup_tables($tables = '*',$zip=true){  
  
    $messages = array();
    $messages['error'] =  0;
    $r='';
    $t = new TableMysql();
    if($tables == '*')   {  
        

        $_DBNAME = CFG::$vars['db']['name'];
        $rows = Table::sqlQuery('SELECT TABLE_NAME AS `Name`, TABLE_ROWS AS `Rows`, (DATA_LENGTH + INDEX_LENGTH)  AS `Size`,  TABLE_COLLATION AS `Collation`  FROM information_schema.TABLES WHERE TABLE_SCHEMA = \''.$_DBNAME.'\' ORDER BY `Name` ASC');
        $tables = array();  

        foreach ($rows as $k => $v){ 
            $tables[] = $v['Name'];       //       $result['rows'][] = ['Tabla'=>$v['Name'] , 'Filas'=>$v['Rows']]; }  // 'Filas'=>Str::formatBytes($v['Rows'])]; }
        }

        /*
        $tables[] = TB_CFG;
        $tables[] = TB_TPL;
      //$tables[] = TB_SLIDER;
        $tables[] = TB_LANG;
        $tables[] = TB_PAGES;
        $tables[] = TB_STR;
        $tables[] = TB_CC;
      //$tables[] = TB_PAIS;
      //$tables[] = TB_PROVNCIA;
      //$tables[] = TB_MUNICIPIO;
      //$tables[] = TB_LOCALIDAD;
        $tables[] = TB_USER;
        $tables[] = TB_ITEM;
        $tables[] = TB_ACL_ROLES;
        $tables[] = TB_ACL_PERMISSIONS;
        $tables[] = TB_ACL_ROLE_PERMS;
        $tables[] = TB_ACL_USER_PERMS;
        $tables[] = TB_ACL_USER_ROLES;
        $tables[] = TB_ACL_ITEM_ROLES;
        */
    }else{  
        $tables = is_array($tables) ? $tables : explode(',',$tables);  
    }  
    
     $return = "-- Backup database ".CFG::$vars['db']['name'];
    //return true;
    foreach($tables as $table) {  

        if (strpos($table,'LOCALID')>0) continue;
        //if (strpos($table,'MUNICIP')>0) continue;
        if (strpos($table,'ONT_')>0) continue;
     //Vars::debug_var($table);
        if (strpos($table,'_LOG')>0) continue;
    
        $return .=  "\n\n\n-- backup table $table\n";

        // ... /control_panel/ajax/update/backdb
        //echo "\n\n\n-- backup table $table\n<br>";
        //continue;

        
        $x = $t->sql_query('SHOW CREATE TABLE '.$table);
        $y = $x[0];
        $return .= "\n".$y['Create Table'].";";
        
        $fields=array();
        $fields_res = $t->sql_query("DESCRIBE {$table}");
        if ($fields_res){ //FIX
            foreach($fields_res as $fields_row){
                $fields[]=$fields_row;  // Field, Type, Null, Kay, Default, Extra
            }
        }
        $num_fields=count($fields);
        $result = $t->sql_query('SELECT * FROM '.$table);
        
        //$row_count = $result->rowCount();
        //if($row_count>0){
        if($result){
            $max = 9000;
            $n=0;
            $return .= "\n". "\nINSERT INTO ".$table." VALUES ";  
            foreach($result as $row){
                      $tmp_str = '';
                      if($n==0) 
                          $tmp_str .=  ""; 
                      else
                          $tmp_str .= ", ";
                      $tmp_str .= "\n(";  
                      $values = array();
                      foreach($fields as $col)   {  
                          $fieldname = $col['Field'];
                          if (is_null ($row[$fieldname] ))  
                              $values[$fieldname] ='NULL';
                          else if ( str2type($col['Type']) == 'int' )
                              $values[$fieldname] = $row[$fieldname];  
                          else
                              $values[$fieldname] = "'".escape($row[$fieldname])."'";  
                      }  
                      $tmp_str .=  implode(', ',$values);
                      $tmp_str .=  ")";  
                      
                      //$n++;
                      $n = $n + strlen($tmp_str);

                      if ($n>=$max){
                          $tmp_str .=  ";\nINSERT INTO ".$table." VALUES ";  
                          $n=0;
                      }
                      $return .= $tmp_str;
            }  
            $return .=  ";";  
        }else{
            $return .=  "\n\n-- No hay filas en la tabla $table\n";
        }

    }  
    //echo '<pre>';  
    //echo $return;
    //echo '</pre>';  

    $backup_file_name = 'database-'.Str::sanitizeName($_SERVER['SERVER_NAME'],true).'-'.date('Ymd-his',time()).'.sql'; 

    $handle = fopen($backup_file_name,'w+');  
    fwrite($handle,$return);  
    fclose($handle);  
    if($zip){
        $zip_file = $backup_file_name.'.zip';
        include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');
        $hzip = createZipFile();
        addToZip($hzip,$backup_file_name);
        saveZipFile($hzip,$zip_file);
        $messages['url'][] = $zip_file;
        sleep(2);
        unlink($backup_file_name);
    }else{
        $messages['url'][] = $backup_file_name;
    }

    //$messages['msg'][] =  " Downloading $file_source <br>";     
    return $messages;

}  

function create_tables_php_file(){  
  
    $messages = array();
    $messages['error'] =  0;
    $r='';
    $t = new TableMysql();

        $tables = array();  
        //$tablas = $t->sql_query('SHOW TABLES');  
        //foreach($tablas as $k => $v) $tables[] = $v[0];
        
        $tables[] = TB_ACL_PERMISSIONS;
        $tables[] = TB_ACL_ROLES;
        $tables[] = TB_ACL_ROLE_PERMS;
        $tables[] = TB_ACL_USER_PERMS;
        $tables[] = TB_ACL_USER_ROLES;
        $tables[] = TB_ACL_ITEM_ROLES;

        $tables[] = TB_USER;
        $tables[] = TB_ITEM;
        
        $tables[] = TB_LANG;
        $tables[] = TB_STR;
        $tables[] = TB_CC;
        $tables[] = TB_TPL;        
        $tables[] = TB_CFG;
        $tables[] = 'CFG_EXTRA_FIELDS';
        
        $tables[] = TB_PAGES;
        $tables[] = TB_PAGES.'_FILES';
        $tables[] = 'CLI_TAGS';
        $tables[] = 'LOG_EVENTS';
        $tables[] = 'CLI_CATEGORIES';

        
        /*
        $tables[] = 'CLI_AGENCIAS';
        $tables[] = 'CLI_COUPONS';
      //$tables[] = 'CLI_CUSTOMERS'; // IS A VIEW
        $tables[] = 'CLI_DESTINOS';
        $tables[] = 'CLI_ORDERS';
        $tables[] = 'CLI_ORDER_LINES';
        $tables[] = 'CLI_PRODUCTS';
        $tables[] = 'CLI_PRODUCT_IMAGES';
        $tables[] = 'CLI_PRODUCT_PRODUCTS';
        $tables[] = 'CLI_PRODUCT_SIZES';
        $tables[] = 'CLI_TARIFAS';
        $tables[] = 'CLI_TAX';
        $tables[] = TB_PAIS;
        $tables[] = TB_PROVNCIA;
        $tables[] = TB_MUNICIPIO;
      //$tables[] = TB_LOCALIDAD;
        */

        /*
        $tables[] = TB_SLIDER;      
        $tables[] = 'GES_BANNERS';
        $tables[] = 'GES_BANNERS_TYPES';
      //$tables[] = TB_USER_ADDRESSES
      //$tables[] = TB_USER_CONTACTS
        */
    $return  =  '<?php'."\n\n";
    $return .= "// -- Create tables for database ".CFG::$vars['db']['name']."\n";
    //Vars::debug_var($tables);
    //return true;
    $primary_keys=true; // in_array(TABLENAMES)
    foreach($tables as $table) {  

        //if (strpos($table,'LOCALID')>0) continue;
        //if (strpos($table,'MUNICIP')>0) continue;
        
        $return .=  "\n\n// -- Create table $table\n";

        
        $x = $t->sql_query('SHOW CREATE TABLE '.$table);
        $y = $x[0];
        //$return .= "\n".  '$sqls[] = "'. $y['Create Table'].";".'"'."\n\n";
        
        $str_fields = '';
        $comma ='';

        $fields=array();
        $fields_res = $t->sql_query("DESCRIBE {$table}");
        if ($fields_res){
            $return .= "\n".  '$sqls[] = "'. 'DROP TABLE IF EXISTS `'.$table.'`";'."\n";
            $return .= "\n".  '$sqls[] = "'. 'CREATE TABLE `'.$table.'` ('  ."\n";
            $str_uni ='';
            $str_key='';
            foreach($fields_res as $fields_row){

                if($fields_row['Field']=='CREATED_BY')      continue;
                if($fields_row['Field']=='CREATION_DATE')   continue;
                if($fields_row['Field']=='LAST_UPDATED_BY') continue;
                if($fields_row['Field']=='LAST_UPDATE_DATE') continue;

                $fields[]=$fields_row;    
                if($primary_keys || $fields_row['Extra']!='auto_increment') {
                    $str_fields .= $comma.$fields_row['Field'];
                    $comma=',';
                }
                //echo '<pre>';
                //print_r($fields_row);
                //echo '</pre>';
                $return .= '`'.$fields_row['Field'].'` '.$fields_row['Type']
                        . ($fields_row['Null'] =='NO'   ? ' NOT NULL':'')
                        . ($fields_row['Default'] ? ' DEFAULT \''.$fields_row['Default'].'\'':'')
                        . ($fields_row['Extra']   ? ' '.$fields_row['Extra']:'')
                        .",\n";
                if($fields_row['Extra']=='auto_increment') $str_key  = 'PRIMARY KEY (`'.$fields_row['Field'].'`)';
                if($fields_row['Key']=='UNI')              $str_uni .= ', UNIQUE KEY (`'.$fields_row['Field'].'`)';
            }
            $return .= $str_key .$str_uni . ") ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci".'";'."\n";  
        }

        $return .=  "\n// -- Rows for table $table\n";


        $num_fields=count($fields);
        $result = $t->sql_query('SELECT * FROM '.$table);
        
      //  $row_count = $result->rowCount();  //SQLite ??????????
        $row_count = $t->recordCount($table);
        if($row_count>0){
            $max = 9000;
            $n=0;
            $return .= "\n".  '$sqls[] =  "INSERT INTO '.$table." (".$str_fields.") VALUES";  
            foreach($result as $row){
                      $tmp_str = '';
                      if($n==0) 
                          $tmp_str .=  ""; 
                      else
                          $tmp_str .= ", ";
                      $tmp_str .= "\n(";  
                      $values = array();
                      foreach($fields as $col)   {  
                          if(!$primary_keys) if($col['Extra']=='auto_increment') continue;
                          $fieldname = $col['Field'];

                          if($fieldname=='CREATED_BY')       continue;
                          if($fieldname=='CREATION_DATE')    continue;
                          if($fieldname=='LAST_UPDATED_BY')  continue;
                          if($fieldname=='LAST_UPDATE_DATE') continue;

                          if (is_null ($row[$fieldname] ))  
                              $values[$fieldname] ='NULL';
                          else if ( str2type($col['Type']) == 'int' )
                              $values[$fieldname] = $row[$fieldname];  
                          else
                              $values[$fieldname] = "'".escape($row[$fieldname])."'";  
                      }  
                      $tmp_str .=  implode(', ',$values);
                      $tmp_str .=  ")";  
                      
                      //$n++;
                      $n = $n + strlen($tmp_str);

                      if ($n>=$max){
                          $tmp_str .=  '";' . "\n" . '$sqls[] =  "INSERT INTO '.$table." (".$str_fields.") VALUES ";  
                          $n=0;
                      }
                      $return .= $tmp_str;
            }  
            $return .=  '";'."\n";  
        }else{
            $return .=  "// -- No hay filas en la tabla $table\n";
        }
        
    }  
    //echo '<pre>';  
    //echo $return;
    //echo '</pre>';  

    $backup_file_name = '_modules_/install/create_tables.php'; 

    $handle = fopen($backup_file_name,'w+');  
    fwrite($handle,$return);  
    fclose($handle);  
    $messages['msg'][] =  " Downloading $backup_file_name <br>";     
    return $messages;

}  


















