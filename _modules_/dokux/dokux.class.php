<?php

//require './vendor/autoload.php';

class Dokux{


    public static function gettext($filename){
        
        $ret = 0;
        
        $path = pathinfo($filename);
        $basename = $path['basename'];
        $filename = $path['filename'];
        $ext = $path['extension'];
        $file_name = DOKUX_INBOX_DIR.'/'.$basename;
        $file_name_out = DOKUX_INBOX_OCR.'/'.$filename.'.txt';

        if(file_exists($file_name)){

                if($ext=='pdf'){

                    if(file_exists($file_name_out)){

                        $text = self::read_file_text($file_name_out);
                    
                    }else{

                        $cmd     = 'pdftotext  '.$file_name.' '.$file_name_out;
                        //////////////////////////////////////////////$cmd_output = shell_exec($cmd);
                        $filesize = filesize($file_name_out);
                        if ($filesize>5) {
                            $text = self::read_file_text($file_name_out);     
                            if ( trim($text)=='' || substr($text,0,4)=='' ) { 
                                unlink($file_name_out);  //FIX check file is deleted
                                $text = false;
                            } 
                        } else {
                            unlink($file_name_out);  //FIX check file is deleted
                            $text = false;
                        }

                        /**
                        $parser = new \Smalot\PdfParser\Parser();
                        try{
                         $pdf  = $parser->parseFile($file_name);
                            $details  = $pdf->getDetails();
                            $text = '';
                            foreach ($details as $property => $value) {
                                if (is_array($value)) {
                                    $value = implode(', ', $value);
                                }
                                $text .= $property . ' => ' . $value . "\n";
                            }
                            //$text = $pdf->getText();
                        }catch (Exception $e) {
                            $text = false; //'No se puede leer este PDF '; 
                        }
                        **/
                    }
                }else if($ext=='zip'){
                    $text = self::getZipFilelist($file_name,$file_name_out);
                }else if($ext=='mp3'){
                    $text = self::getMp3FileInfo($file_name,$file_name_out);
                }else if($ext=='txt'){
                    $text = self::getTXTFile($file_name,$file_name_out);
                }else if($ext=='csv'){
                    $text = self::getCSVFile($file_name,$file_name_out);
                }else if($ext=='jpg' || $ext=='webp'){
                    $text = false;
                }else {
                    $text = $ext.' = > '.$file_name;
                }

                if($text){
                    if($hfp = fopen($file_name_out,'w'))  fwrite($hfp,stripslashes($text));
                    fclose($hfp);
                    $ret = 1;                    
                }

        }else{
           // $ajax_result['text'] = 'ERROR No existe el archivo '.$file_name; 
        }
          
        return $ret;

    }


    public static function parseDir($dir,$recursive=false){

        $files = array();

        $fileList = glob(DOKUX_INBOX_DIR.'/*');
        foreach($fileList as $file){
            if(is_file($file)){

                $path = pathinfo($file);
                $basename = $path['basename'];
                $filename = $path['filename'];
                $ext = $path['extension'];
                $key = hash('crc32',$basename);

                if( file_exists(DOKUX_INBOX_OCR.'/'.$filename.'.txt') || file_exists(DOKUX_INBOX_OCR.'/'.$filename.'-0.txt') )  { 
                    $ocr='1'; 
                }else {
                     $ocr = self::gettext($basename);
                }
                $files[] = ['name'=>$basename,'ext'=>$ext, 'ocr'=>$ocr]; 

            }else if(is_dir($file)){
                if($recursive){
                    //echo 'DIR: '.$file, '<br>'; 
                    $files = array_merge($files,self::parseDir($file));
                }
            }   

        }

        return $files;

    }

    public static function file2base64($filename){
        return base64_encode(fread(fopen($filename, "r"), filesize($filename)));
    }
    
    public static function escape($value) {                 
        //$search = array("\\",  "\x00",  "'",  '"', "\x1a" );     // "\n",  "\r",  ci�n
        //$replace = array("\\\\","\\0", "\'", '\"', "\\Z" );     //"\\n", "\\r",ol�ti
        $search = array("\\",  "\x00",  "'",  '"', "\x1a" );     // "\n",  "\r",  ci�n
        $replace = array("\\\\","\\0", "\'", '\"', "\\Z" );     //"\\n", "\\r",ol�ti
        return  Encoding::toUTF8(str_replace($search, $replace, $value));
    }                                                                      

    public static function read_file_text($filename){
        $ret = '';
        foreach(file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if(trim($line)!='')$ret .=  $line."\n";
        }
        return $ret;
    }

    /***********

    function datos_selene($params) {
        $result = array();
        $result['error'] = 0;
        $result['msg'] = 'ok';
        $to = new TableOracle();
        //$sql = "SELECT to_char(trunc((SYSDATE - DATEOFBIRTH) / 365)) AS EDAD FROM PACIENTE WHERE NHC = '{$params['id']}'";
        $sql = "SELECT NAME, SURNAME, SURNAME2, SEX, MEDICALRECORD, to_char(trunc((SYSDATE - DATEOFBIRTH) / 365)) AS EDAD FROM PACIENTE WHERE NHC = '{$params['id']}'";
        //$sql = "SELECT DATEOFBIRTH EDAD FROM PACIENTE WHERE NHC = '{$params['id']}'";
        $result['sql'] = $sql;
        $paciente = $to->asArray($sql);
        $result['paciente'] = $paciente[0];
        echo json_encode($result);     
    }

    ***********/

    public static function text2doctype($text){
        $ret = ['ID'=>'0','NAME'=>'Desconocido'];
        $filetypes = Table::sqlQuery("SELECT ID,NAME,EXP,EXP_IDENTIFIER FROM DOKU_FILETYPES WHERE EXP<>'' AND (SERVICIO = ".$_SESSION['DOKU_AREA']['ID']." OR SERVICIO='0') AND ACTIVE=1 ORDER BY PRIORITY");
        foreach($filetypes as $type){
            if(preg_match_all($type['EXP'], $text)>=1) {
                $ret['ID'] = $type['ID'];
                $ret['NAME'] = $type['NAME'];
                preg_match($type['EXP_IDENTIFIER'], $text, $match);
                $ret['IDENTIFIER'] =  $match[0];
                break;
            }
        }
        return $ret;
    }

    public static function text2identifier($text){
        $identifier ='';
        $regidentifier='/((([X-Z])|([LM])){1}([-]?)((\d){7})([-]?)([A-Z]{1}))|((\d{8})([-]?)([A-Z]))/';
        preg_match($regidentifier, $text,$matchesidentifier); // Devuelve 1
        $identifier = $matchesidentifier[0];
        if($identifier==''){
            $regnhc =  '~NHC(:*)\s*\K\d+~';
            preg_match($regnhc, $text, $matchesnhc); // Devuelve 1
            $identifier = $matchesnhc[0];
        }
        return $identifier;
    }


    public static function getTXTFile($txtfile,$file_name_out=false){
        $text='';        
        $text = self::read_file_text($txtfile);     
        /**
        if($text!='' && $file_name_out){
            if($hfp = fopen($file_name_out,'w'))  fwrite($hfp,stripslashes($text));
            fclose($hfp);
        }
        **/
        return $text;
    }

    public static function getCSVFile($csvfile,$file_name_out=false){
        $html='';        
        foreach(file($csvfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if(trim($line)!='')$html .=  '<tr><td>'.str_replace(';','</td><td>',$line)."</td></tr>\n";
        }       
        return '<table>'.$html.'</table>';
    }

    public static function getZipFilelist($zipfile,$file_name_out=false){
        $text='';        
        if ($zip = zip_open($zipfile)) {
            if ($zip) {
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
                  $text .= zip_entry_name($zip_entry)."\n";
                  /*
                  $fp=fopen("./".zip_entry_name($zip_entry),"w");
                  fwrite($fp,$buf);
                  zip_entry_close($zip_entry);
                  */
                }//  else $text .= 'cant open zip entry'.$zipfile;
              }
              zip_close($zip);
            }
        } else $text = 'cant open '.$zipfile;

        if($text!='' && $file_name_out){
            if($hfp = fopen($file_name_out,'w'))  fwrite($hfp,stripslashes($text));
            fclose($hfp);
        }

        return $text;

    }

    public static function getMp3FileInfo($mp3file,$file_name_out=false){

        include_once(SCRIPT_DIR_LIB.'/getID3/getid3/getid3.php');

        $text='';        
        $getID3 = new getID3;
        $ThisFileInfo = $getID3->analyze($mp3file);
        $getID3->CopyTagsToComments($ThisFileInfo);
        $hash = sha1_file($mp3file);
        $path = pathinfo($file_name_out);
        $filename = $path['filename'];   // filename
        $basename = $path['basename'];   // filename.ext
        $ext      = $path['extension'];  // ext
        //$key = hash('crc32',$basename);

        //$search = '';
        foreach($ThisFileInfo['comments_html'] as $k=>$v){ 
            $text .= $k.': '.$v[0]."\n";
            //if($k=='artist'||$k=='album'||$k=='title')$search.=$v[0].' ';
        }
        foreach($ThisFileInfo['audio']['streams'][0] as $k=>$v){ $text .= $k.': '.$v."\n"; }
        $text .= 'Playtime: '.$ThisFileInfo['playtime_string']."\n";
        /*
        if(isset($ThisFileInfo['comments']['picture'][0])){
            $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
            $text .= 'Thumb<br /><img id="FileImage" width="150" src="'.@$Image.'" height="150">';
        }
        **/  
        //$search_file = DOKUX_INBOX_OCR.'/'.$hash.'.txt';
        //file_put_contents($search_file, $search);
        if (isset($ThisFileInfo['comments']['picture']['0']['data'])) {
            $image = $ThisFileInfo['comments']['picture']['0']['data'];
            $img_file = DOKUX_FILES_TMB.'/'.$hash.'.jpg';
          //$img_src = 'https://web.com/{APP_NAME}/raw/path=ocr/mode=inline/filename='.$filename.'/name=thumb/ext=jpg';
            $img_src = '/'.APP_NAME.'/raw/path=thumb/mode=inline/filename='.$hash.'/name=thumb/ext=jpg';
            if(file_put_contents($img_file, $image)) {
               $text .= '[IMAGE]';  //Imagen: <br /><img id="thumb" style="width:200px;" src="'.$img_src.'">';
            } else {
               // $text .= 'Sin imagen'."\n";
            }
        }
        
        if($text!='' && $file_name_out){
            if($hfp = fopen($file_name_out,'w'))  fwrite($hfp,stripslashes($text));
            fclose($hfp);
        }

        return $text;
    }

}

