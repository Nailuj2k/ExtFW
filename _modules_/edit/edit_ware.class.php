<?php
    
    function cmp($a, $b)   {

        //  if ($a == $b) {
        //      return 0;
        //  }

        //  if ( substr($a['basename'],0,1)=='_') return -1; else return 1;

        $aa = str_replace('_','0',$a['basename']);
        $bb = str_replace('_','0',$b['basename']);

        //return strcmp($aa, $bb) ? 1 : -1;
        return ($aa < $bb) ? -1 : 1;

    }
    
    class EDIT_ware{
        
         public static function parseDir($dir,$recursive=false){

             //$dir = str_replace('//','/',$dir);
             //Vars::debug_var($dir);
             $files = array();
             $folders = array();
             $dirsize = 0;
             $nfiles = 0;

             $fileList = glob($dir.'/{,.}[!.,!..]*',GLOB_MARK|GLOB_BRACE);
             foreach($fileList as $file){
                  $file = str_replace('//','/',$file);
                  $path     = pathinfo($file);
                  if($path['extension']=='htaccess') continue;
                  $basename = $path['basename'];
                  $id       = md5($file);
                  //$id_path  = md5(dirname($path['dirname']));
                  $nfiles++;
                
                  if(is_file($file)){

                      $filename = $path['filename'];
                      $ext      = $path['extension'];
                      $key      = hash('crc32',$basename);
                      $size     = filesize($file);
                      $dirsize += $size;
                      if(in_array($ext,['php','css','html','js','txt','htaccess','json', 'sqlite','log',
                                        'sql','lock','py','csv','md',
                                        'jpg','gif','png','svg','webp','ttf','eot',
                                        'psd','ai','tiff','bmp',
                                        'mp3','ogg','wav','doc','docx','xls','xlsx','mkv', 'wav','mp4','epub','pdf','zip','gz'])){

                           if     (in_array($ext,['php','css','html','js','txt','htaccess','json','sql','lock','py','md','log'])) $type = 'code'; 
                           else if(in_array($ext,['jpg','gif','png','svg','webp']))                                    $type = 'image'; 
                           else if(in_array($ext,['psd','ai','tiff','bmp']))                                           $type = 'picture'; 
                           else if(in_array($ext,['eot','ttf','otf']))                                                 $type = 'font'; 
                           else if(in_array($ext,['mp3','ogg','wav']))                                                 $type = 'audio'; 
                           else if(in_array($ext,['doc','docx','xls','xlsx']))                                         $type = 'office'; 
                           else if(in_array($ext,['mkv','wav','mp4']))                                                 $type = 'video'; 
                           else if(in_array($ext,['epub']))                                                            $type = 'ebook'; 
                           else if(in_array($ext,['pdf']))                                                             $type = 'pdf'; 
                           else if(in_array($ext,['zip','gz']))                                                        $type = 'zip'; 
                           else if(in_array($ext,['csv']))                                                             $type = 'csv'; 

                           $files[]  = [     'id'=>$id,
                                        'id_path'=>$id_path,
                                           'path'=>$dir,
                                   //   'id_path'=>$id_path,
                                           'file'=>$file,
                                       'basename'=>$basename,
                                       'filename'=>$filename,
                                           'type'=>$type,
                                            'ext'=>$ext,
                                           'size'=>$size  ]; 

                      }

                  }else if(is_dir($file)){

                      if($recursive){
                           $parseDir = self::parseDir($file,$recursive);
                           $dirsize += $parseDir['size'];
                           $folders[] = ['id'=>$id,
                                       /*'id_path'=>$id_path,*/
                                         'path'=>$dir,
                                         'file'=>$file,
                                         'basename'=>$basename, 
                                         'size'=>$parseDir['size'], 
                                         'ext'=>'DIR', 
                                         'files'=>$parseDir['files'], 
                                         'nfiles'=>$parseDir['nfiles']]; 
                      }

                  }   

             }
             
             usort($folders, "cmp");
             //sort($files);
             usort($files, "cmp");
             $files = array_merge($folders,$files);
             //sort($files,SORT_STRING );

             return ['files' => $files, 'size'=>$dirsize, 'nfiles'=>$nfiles ];
             //Str::formatBytes($size)
         }



        public static function search($dir, $query, $recursive = true){

             $results = array();
             $totalMatches = 0;

             if (empty($query)) return ['files' => $results, 'matches' => 0];

             $fileList = glob($dir.'/{,.}[!.,!..]*', GLOB_MARK|GLOB_BRACE);
             //$limit = 67;
             //$count =0;
             foreach($fileList as $file){

                  //$count++;
                  //if ($count > $limit) break;

                  $file = str_replace('//', '/', $file);
                  $path = pathinfo($file);
                  if ($path['extension'] == 'htaccess') continue;

                  $basename = $path['basename'];
                  $id = md5($file);

                  if (is_file($file)){

                      $ext = $path['extension'] ?? '';

                      // Solo buscar en archivos de texto/código
                      if (!in_array($ext, ['php','css','html','js','txt','htaccess','json',/*'log',*/'sql','lock','py','csv','md'])) continue;

                      // Leer contenido del archivo y buscar query en cada línea
                      $lines = @file($file, FILE_IGNORE_NEW_LINES);
                      if ($lines === false) continue;

                      $lineNumber = 0;
                      foreach ($lines as $lineContent) {
                          $lineNumber++;

                          // Buscar query en la línea (case-insensitive)
                          if (stripos($lineContent, $query) !== false) {
                              $totalMatches++;

                              // Recortar línea si es muy larga
                              $displayLine = strlen($lineContent) > 200
                                  ? substr($lineContent, 0, 200) . '...'
                                  : $lineContent;

                              $results[] = [
                                  'id'       => $id,
                                  'file'     => $file,
                                  'path'     => $dir,
                                  'basename' => $basename,
                                  'ext'      => $ext,
                                  'line'     => $lineNumber,
                                  'content'  => mb_convert_encoding(trim($displayLine), 'UTF-8', 'UTF-8')
                              ];
                          }
                      }

                  } else if (is_dir($file)){

                      if ($recursive) {
                           $parseDir = self::search($file, $query, $recursive);
                           $results = array_merge($results, $parseDir['files']);
                           $totalMatches += $parseDir['matches'];
                      }

                  }
             }

             // No ordenar, mantener orden por archivo y línea

             return ['files' => $results, 'matches' => $totalMatches];
         }




        public static function read_file_text($filename){
            /**
            $ret = '';
            foreach(file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $ret .=  $line."\n";
            }
            return $ret;
            */
            $ret = file_get_contents($filename);
            if ($ret === false) return "==== NOT READABLE ====\n";
            if(empty($ret)) return "==== EMPTY FILE ====\n";
            else return $ret;

        }

        public static function read_file_zip($zipfile){  //},$file_name_out=false){
            
            $path     = pathinfo($zipfile);
            $basename = $path['basename'];

            $text='<h5>Files in <b>'.$basename.'</b></h5>';        
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
                      //$fp=fopen("./".zip_entry_name($zip_entry),"w");
                      //fwrite($fp,$buf);
                      //zip_entry_close($zip_entry);
                    }//  else $text .= 'cant open zip entry'.$zipfile;
                  }
                  zip_close($zip);
                }
            } else $text = 'cant open '.$zipfile;
    
            //if($text!='' && $file_name_out){
            //    if($hfp = fopen($file_name_out,'w'))  fwrite($hfp,stripslashes($text));
            //    fclose($hfp);
            //}
            return '<div style="padding:10px;background-color:var(--dark);color:white;white-space:pre;font-size:0.8em;">'.$text.'</div>';
            //return $text;
           
        }        

        public static function read_file_gz($nombreArchivo) {
            if (!file_exists($nombreArchivo)) {
                return ["error" => "El archivo no existe"];
            }

            $info = [
                "nombre_archivo" => basename($nombreArchivo),
                "tamano_comprimido" => Str::formatBytes(filesize($nombreArchivo)),
                "es_tar_gz" => false,
                "archivos_contenidos" => [],
                "metadatos_gzip" => []
            ];

            // Intentar leer como tar.gz (archivo comprimido múltiple)
            try {
                $phar = new PharData($nombreArchivo, 0, null, Phar::TAR);
                $info["es_tar_gz"] = true;
                
                foreach (new RecursiveIteratorIterator($phar) as $archivo) {
                    if ($archivo->isFile()) {
                        $info["archivos_contenidos"][] = [
                            "nombre" => $archivo->getFilename(),
                            "tamano" => Str::formatBytes($archivo->getSize())
                        ];
                    }
                }
            } catch (Exception $e) {
                // No es un tar.gz, procesar como gz simple
            }

            // Obtener metadatos del gzip
            $gz = gzopen($nombreArchivo, 'rb');
            $info["metadatos_gzip"] = [
                "formato" => "gzip",
                "version" => ord(gzgetc($gz)), // Versión del formato
                "sistema" => ord(gzgetc($gz)) // Sistema operativo origen
            ];
            gzclose($gz);

            // Obtener nombre original del archivo (si está en el header)
            $info["nombre_original"] = self::obtenerNombreOriginalGz($nombreArchivo);

            //return $info;
            return '<div style="padding:10px;background-color:var(--dark);color:white;white-space:pre;font-size:0.8em;">'.print_r($info,true).'</div>';
   
        }

        // Función auxiliar para extraer el nombre original del header gzip
        private static function obtenerNombreOriginalGz($nombreArchivo) {
            $f = fopen($nombreArchivo, 'rb');
            fread($f, 3); // Saltar magic number y versión
            $flags = ord(fread($f, 1));
            
            // Si el flag FNAME está activado (bit 3)
            if ($flags & 8) {
                $nombre = '';
                while (!feof($f) && ($char = fread($f, 1)) != "\x00") {
                    $nombre .= $char;
                }
                return $nombre;
            }
            fclose($f);
            return "No disponible";
        }

        // Ejemplo de uso:
        //$resultado = analizarArchivoGz('archivo_ejemplo.tar.gz');
        //print_r($resultado);


        public static function read_file_csv($csvfile){
            
            $html='';        
            
            $first = true;
            $cell = 'th';
            foreach(file($csvfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = iconv("ISO-8859-1", "UTF-8", $line );
                //$n++;
                //if($n>100) break;
                                   

                if(trim($line)!='')$html .=  '<tr><'.$cell.'>'.str_replace(';','</'.$cell.'><'.$cell.'>',$line)."</'.$cell.'></tr>\n";
                $cell = 'td';
            }       
            
            return '<table class="zebra">'.$html.'</table>';
        }

    }