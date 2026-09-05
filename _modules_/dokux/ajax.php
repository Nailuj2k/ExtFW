<?php 

    include(SCRIPT_DIR_MODULE.'/dokux.class.php');

    $ajax_result = array();
    $ajax_result['error']=0;

    // https://lgslm.medium.com/public-key-cryptography-and-digital-signature-using-openssl-af88474e9d96
    // generate privkey
    //     openssl genpkey -algorithm RSA -out jts27n_rsa -pkeyopt rsa_keygen_bits:2048
    //     openssl genpkey -algorithm RSA -out jts27n_rsa -pkeyopt rsa_keygen_bits:2048 -aes-256-cbc -pass pass:achosi
    // generate pubkey
    //     openssl rsa -in jts27n_rsa -pubout -out jts27n_rsa.pub
    //
    // generamos txt con el hash
    //     echo '27d195ce11ef956bbcf149677b71ae85cabe26fd' > doc1.txt
    // encriptamos el txt con la clave privada
    //     openssl rsautl -sign -inkey jts27n_rsa -keyform PEM -in doc1.txt > doc1.enc.txt
    //
    // Enviamos documento + txt + clave publica 
    //
    // desencriptamos con la clave publica
    //     openssl rsautl -verify -inkey jts27n_rsa.pub -pubin -keyform PEM -in doc1.enc.txt -out doc1.okis.txt
    // y comprobamos
    //     OK = ( doc1.okis.txt === doc1.txt)

    // https://es.linux-console.net/
    // https://es.linux-console.net/?p=53




    
    if(!$doku_user){
        $ajax_result['error'] = 1;
        $ajax_result['msg']   = 'No tiene permisos para usar este módulo';
        echo json_encode($ajax_result);
        die();
    }

  
    if($_ARGS['op']=='parsedir'){
        $ajax_result['info'] =  SYS::Info();
        $ajax_result['files'] = Dokux::parseDir(DOKUX_INBOX_DIR);
        echo json_encode($ajax_result);

    }else if($_ARGS['op']=='test'){

        include(SCRIPT_DIR_MODULE.'/test.php');
      
    /*
    }else if($_ARGS['op']=='ai'){

        $ajax_result['text'] = '<h4>Parse file with AI.......</h4><p>'.print_r($_ARGS,true).'</p>';


        echo json_encode($ajax_result);
    */

    }else if($_ARGS['op']=='ai'){

        $file = DOKUX_INBOX_DIR.'/'.$_ARGS['file'].'.'.$_ARGS['ext'];

        if(file_exists($file)){

            $ajax_result['msg'] = 'Archivo encontrado: '.$file;
       
            $service = $_ARGS['service'] = $_ARGS['service'] ?? 'gemini';

            // dummy no llama a nada y ollama puede correr en local sin autenticacion.
            $ai_no_key = array('dummy','ollama');

            // Solo estos implementan parseFile(); el resto devuelve el array sin tocar,
            // igual que hacen estos cuando la llamada HTTP falla.
            $ai_reads_files = array('gemini','claude','openai','ollama');

            if(!in_array($service,$ai_no_key) && empty(CFG::$vars['ai'][$service]['api_key'])){

                $ajax_result['error'] = 1;
                $ajax_result['msg']   = 'Falta la clave de API del servicio "'.$service.'". '
                                      . 'Defina {<b>ai.'.$service.'.api_key</b>} en configuration.php.';

            }else if(!in_array($service,$ai_reads_files)){

                $ajax_result['error'] = 1;
                $ajax_result['msg']   = 'El servicio "'.$service.'" no lee documentos. '
                                      . 'Use '.implode(' o ', $ai_reads_files).'.';

            }else{

                include(SCRIPT_DIR_MODULES.'/edit/ai/AiServiceInterface.php');

                switch( $service ){
                    case 'claude'  :
                        define('CLAUDE_API_KEY', CFG::$vars['ai']['claude']['api_key']);
                        include(SCRIPT_DIR_MODULES.'/edit/ai/ClaudeAiService.php');
                        $aiService = new \AIServices\ClaudeAiService();
                        break;
                    case 'openai'  :
                        define('OPENAI_API_KEY', CFG::$vars['ai']['openai']['api_key']);
                        include(SCRIPT_DIR_MODULES.'/edit/ai/OpenAiService.php');
                        $aiService = new \AIServices\OpenAiService();
                        break;
                    case 'ollama'  :
                        if(!empty(CFG::$vars['ai']['ollama']['api_key']))      define('OLLAMA_API_KEY', CFG::$vars['ai']['ollama']['api_key']);
                        if(!empty(CFG::$vars['ai']['ollama']['host']))         define('OLLAMA_HOST', CFG::$vars['ai']['ollama']['host']);
                        if(!empty(CFG::$vars['ai']['ollama']['vision_model'])) define('OLLAMA_VISION_MODEL', CFG::$vars['ai']['ollama']['vision_model']);
                        include(SCRIPT_DIR_MODULES.'/edit/ai/OllamaAiService.php');
                        $aiService = new \AIServices\OllamaAiService();
                        break;
                    default        :
                        define('GEMINI_API_KEY', CFG::$vars['ai']['gemini']['api_key']);
                        include(SCRIPT_DIR_MODULES.'/edit/ai/GeminiAiService.php');
                        $aiService = new \AIServices\GeminiAiService();
                }

                $parsed = $aiService->parseFile($file, ['text' => '']);
                $text   = $parsed['text'];

                // Guardar texto en el directorio OCR para que parsedir devuelva ocr=1
                if (!empty($text)) {
                    file_put_contents(DOKUX_INBOX_OCR . '/' . $_ARGS['file'] . '.txt', $text);
                    $ajax_result['text'] = $text;
                } else {
                    $ajax_result['error'] = 1;
                    $detail = (method_exists($aiService,'getLastError') && $aiService->getLastError())
                            ? ' Detalle: '.$aiService->getLastError()
                            : ' Revise la clave de API, el modelo y el log del servidor.';
                    $ajax_result['msg']   = 'El servicio "'.$service.'" no ha devuelto texto para '
                                          . $_ARGS['file'].'.'.$_ARGS['ext'].'.'.$detail;
                }
            }
            /*
            $result = $aiService->parseFile('/ruta/al/factura.pdf', [
                'text'           => '',
                'Importe_total'  => '',
                'fecha'          => '',
                'NIF'            => ''
            ]);
            */
        }else{
                $ajax_result['msg'] = 'Archivo NO encontrado: '.$file;
                $ajax_result['error'] = 1;
        }
        
        header('Content-Type: application/json');
        echo json_encode($ajax_result);


    }else if($_ARGS['op']=='gettext'){

        if (file_exists(DOKUX_INBOX_OCR.'/'.$_ARGS['filename'].'.txt')){

            //$ajax_result['text'] =  file_get_contents( DOKUX_INBOX_OCR.'/'.$_ARGS['filename'].'.txt');
            $ocrtext =  Dokux::read_file_text( DOKUX_INBOX_OCR.'/'.$_ARGS['filename'].'.txt');
            if(trim($ocrtext)==''){
                $ajax_result['msg'] = 'No se puede leer el texto de '.$_ARGS['filename'].'.txt';
                $ajax_result['error'] = 1;
            }else{
                $ajax_result['text'] = Dokux::escape($ocrtext);
            }
            $filename = DOKUX_INBOX_DIR.'/'.$_ARGS['filename'].'.'.$_ARGS['ext'];
            $hash =  sha1_file($filename);
            $ajax_result['hash']=$hash;
            
            $ajax_result['search']='';
            $lines = explode("\n",$ocrtext);
            foreach($lines as $line){
                if(str_starts_with($line,'title:')) $ajax_result['search'] .= str_replace('title:','',$line);
                if(str_starts_with($line,'artist:')) $ajax_result['search'] .= str_replace('artist:','',$line);
                if(str_starts_with($line,'album:')) $ajax_result['search'] .= str_replace('album:','',$line);
            }
            if($ajax_result['search']=='') $ajax_result['search']=str_replace('_',' ',$_ARGS['filename']);


            if(file_exists(DOKUX_FILES_TMB.'/'.$hash.'.jpg'))
                $ajax_result['thumb'] = APP_NAME.'/raw/path=thumb/mode=inline/filename='.$hash.'/name=thumb/ext=jpg';
            else
                $ajax_result['thumb'] = 0;

        }else if (file_exists(DOKUX_INBOX_OCR.'/'.$_ARGS['filename'].'-0.txt')){

            $ocrtext =  Dokux::read_file_text( DOKUX_INBOX_OCR.'/'.$_ARGS['filename'].'-0.txt');
            if(trim($ocrtext)==''){
                $ajax_result['msg'] = 'No se puede leer el texto de '.$_ARGS['filename'].'-0.txt';
                $ajax_result['error'] = 1;
            }else{
                $ajax_result['text'] = Dokux::escape($ocrtext);
            }
            $filename = DOKUX_INBOX_DIR.'/'.$_ARGS['filename'].'.'.$_ARGS['ext'];
            $hash =  sha1_file($filename);
            $ajax_result['hash']=$hash;

        }else if ($_ARGS['ext']=='zip'){

            $row = Table::getFieldsValues('SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id']);
            $ajax_result['title'] = $row['FILE_NAME'];
            $ajax_result['msg'] = $row['NOTES'];
            $ajax_result['text'] = $row['NOTES'];//'SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id'];
      
        }else if ($_ARGS['ext']=='csv'){   //CSV

            $row = Table::getFieldsValues('SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id']);
            $ajax_result['title'] = $row['FILE_NAME'];
            $ajax_result['msg'] = $row['NOTES'];
            $ajax_result['text'] = $row['NOTES'];//'SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id'];
         
        }else if ($_ARGS['ext']=='txt'){

            $row = Table::getFieldsValues('SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id']);
            $ajax_result['title'] = $row['FILE_NAME'];
            $ajax_result['msg'] = $row['NOTES'];
            $ajax_result['text'] = $row['NOTES'];//'SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id'];
         
        }else if ($_ARGS['ext']=='mp3'){

            //  https://extralab.net/ebooks/buscaportadas.php

            $row = Table::getFieldsValues('SELECT ID,TYPE,NOTES,NAME,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id']);
            $ajax_result['title']  = $row['FILE_NAME'];
            $ajax_result['msg']    = $row['NOTES'];
            $ajax_result['text']   = $row['NOTES'];//'SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id'];
            $ajax_result['search'] ='';
            if(file_exists(DOKUX_FILES_TMB.'/'.$row['HASH'].'.jpg'))
              //$ajax_result['thumb'] = '/APP_NAME/raw/path=thumb/mode=inline/filename='.$row['HASH'].'/name=thumb/ext=jpg';
              $ajax_result['thumb'] = APP_NAME.'/raw/path=thumb/mode=inline/filename='.$row['HASH'].'/name=thumb/ext=jpg';
            else
                $ajax_result['thumb'] = 0;
            $lines = explode("\n",$row['NOTES']);
            foreach($lines as $line){
                if(str_starts_with($line,'title:'))  $ajax_result['search'] .= str_replace( 'title:','',$line);
                if(str_starts_with($line,'artist:')) $ajax_result['search'] .= str_replace('artist:','',$line);
                if(str_starts_with($line,'album:'))  $ajax_result['search'] .= str_replace( 'album:','',$line);
            }
            if($ajax_result['search']=='') $ajax_result['search']=str_replace('_',' ',$row['NAME']);
            $ajax_result['hash']=$row['HASH'];

        }else{

           $ajax_result['text'] = '<h4>No existe texto OCR para este documento.......</h4>';

        }
        //echo 'AHORA EL JSON:';
        //Vars::debug_var($ajax_result);
        echo json_encode($ajax_result);

    }else if($_ARGS['op']=='getthumbs'){
        $ajax_result['thumbs']=array();
        include SCRIPT_DIR_LIB.'/simplehtmldom/simple_html_dom.php';
        //$_ARGS['search']='henry mancini peter gunn';
        $search =  str_replace(' ','+',$_ARGS['search']); //str_replace(' ','+',trim(Dokux::read_file_text( DOKUX_INBOX_OCR.'/'.$_ARGS['hash'].'.txt')));
        $url_search = "https://www.google.com/search?q={$search}&tbm=isch"; //
        $ajax_result['url_search'] = $url_search;
        $html = file_get_html($url_search);
        $images = $html->find('img'); //.t0fcAb');
        $image_count = 10;
        $i = 0;
        foreach($images as $image){
            if($i == $image_count) break;
            $i++;
            if($i < 2) continue;
            $ajax_result['thumbs'][]=$image->src;
            $html->clear();
        }
        ///APP_NAME/ajax/op=getthumbs/hash=08888af5f6b93e10154fa5d4e5ffea92ea3a2ab4
        //Vars::debug_var($ajax_result['thumbs']);
        echo json_encode($ajax_result);
    }else if($_ARGS['op']=='saveurl'){
        
        $url = $_ARGS['url'];
        $filename = $_ARGS['filename'];
        file_put_contents($filename, file_get_contents($url));

        /**
        $ch = curl_init('http://example.com/image.php');
        $fp = fopen('/my/folder/flower.gif', 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        **/
        echo json_encode($ajax_result);
       /**********
    }else if($_ARGS['op']=='readzip'){


        $zipfile = DOKUX_INBOX_DIR.'/'.$_ARGS['filename'].'.zip';
        $ajax_result['text']='';        
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
                  $ajax_result['text'] .= zip_entry_name($zip_entry)."\n";
                  // $fp=fopen("./".zip_entry_name($zip_entry),"w");
                  // fwrite($fp,$buf);
                  // zip_entry_close($zip_entry);
                }  else $ajax_result['text'] = 'cant open zip entry'.$zipfile;
              }
              zip_close($zip);
            }
          } else $ajax_result['text'] = 'cant open '.$zipfile;

          Vars::debug_var($ajax_result['text'] );
          echo json_encode($ajax_result);
          *******/
    }else if($_ARGS['op']=='maximize'){

         Vars::setSessionVar('DOKU_TABLE_FILES_ROWS',Vars::getArrayVar($_SESSION,'DOKU_TABLE_FILES_ROWS')==DOKU_FILES_ROWS?DOKU_FILES_MAX_ROWS:DOKU_FILES_ROWS  );
         $ajax_result['rows'] = Vars::getArrayVar($_SESSION,'DOKU_TABLE_FILES_ROWS');
         $ajax_result['msg'] = 'ROWS '. Vars::getArrayVar($_SESSION,'DOKU_TABLE_FILES_ROWS'); 
         echo json_encode($ajax_result);

    }else if($_ARGS['op']=='changearea'){

        /*
        function _ta($rows){
            $result=[];
            foreach($rows as $row) { 
                $result[$row['ID']] = $row['NAME'];
            }
            return $result;
        } */
        //$myareas = _ta(AreasACL::getAreas($_SESSION['userid']));
    

        $myareas =AreasACL::getAreas($_SESSION['userid']);
        /*

        $aaa=[];
        foreach($myareas as $row) { 
            $aaa[$row['ID']] = $row['NAME'];
        }
        */

        //   $sql = "SELECT ID,NAME,AREAKEY FROM DOKU_AREAS WHERE ACTIVE=1";
        //   $myareas = Table::asArrayValues($sql,'ID',false);
        //$myareas = Dokux::getAreasUser();
        Vars::setSessionVar('DOKU_AREA', ['ID' => $_ARGS['id'], 'NAME' => $myareas[$_ARGS['id']]] );

        $ajax_result['msg'] = 'AREA '. $_SESSION['DOKU_AREA']['NAME']; 
        $ajax_result['key'] =  $_SESSION['DOKU_AREA']['ID']; 
        $ajax_result['id'] = $_SESSION['DOKU_AREA']['ID']; 

        echo json_encode($ajax_result);

    }else if($_ARGS['op']=='getpdf'){

        $file_dir = $_ARGS['dir'] == 'inbox' ? DOKUX_INBOX_DIR : DOKUX_FILES_DIR;
        $ajax_result['base64file'] = Dokux::file2base64($file_dir.'/'.$_ARGS['file']);
        $ajax_result['pdf_text'] = 'No se ha encontrado texto en éste PDF.';
        echo json_encode($ajax_result);

    }else if($_ARGS['op']=='fileviewtext'){

        $row = Table::getFieldsValues('SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id']);
        $ajax_result['title'] = $row['FILE_NAME'];
        $ajax_result['msg'] = $row['NOTES'];
        echo json_encode($ajax_result);
      
    }else if($_ARGS['op']=='fileproccess'){

        $row = Table::getFieldsValues('SELECT ID,TYPE,NOTES,FILE_NAME,HASH,VALIDATED FROM DOKU_FILES WHERE ID = '.$_ARGS['id']);
        if($row['VALIDATED']==1){
            $ajax_result['msg'] = 'Este documento está validado. No se puede modificar';
            $ajax_result['updated']=0;
        }else{
            $ajax_result['old_doc_type'] = $row['TYPE'];
            $ocrtext    = Str::removeStopWords($row['NOTES']);

            $doc_type   = Dokux::text2doctype($ocrtext);
            $identifier = Dokux::text2identifier($ocrtext);          


            $ajax_result['doc_type_name'] = $doc_type['NAME'];
            $ajax_result['new_doc_type']  = $doc_type['ID'];

            $identifier = $doc_type['IDENTIFIER']? $doc_type['IDENTIFIER'] : $identifier;


            if($ajax_result['old_doc_type']!=$ajax_result['new_doc_type']){
                Table::sqlExec('UPDATE DOKU_FILES SET TYPE='.$ajax_result['new_doc_type'].' WHERE ID = '.$_ARGS['id']);
                $ajax_result['id'] = $row['ID'];
                $ajax_result['updated']=1;
            }
            if($identifier!==''){
                Table::sqlExec("UPDATE DOKU_FILES SET IDENTIFIER='{$identifier}' WHERE ID = ".$_ARGS['id']);
                $ajax_result['id'] = $row['ID'];
                $ajax_result['updated']=1;
                $ajax_result['identifier']=$identifier;
            }
            $hash = sha1_file(DOKUX_FILES_DIR.'/'.$row['FILE_NAME']);
            if($hash!=$row['HASH']){
                Table::sqlExec("UPDATE DOKU_FILES SET HASH='{$hash}' WHERE ID = ".$_ARGS['id']);
                $ajax_result['id'] = $row['ID'];
                $ajax_result['updated']=1;
                $ajax_result['hash']=hash('crc32',$hash);
            }
            if ($_ARGS['ext']=='csv'){
                $ajax_result['filename'] = DOKUX_FILES_DIR.'/'.$row['FILE_NAME'];
                $ajax_result['text'] = Dokux::getCSVFile($ajax_result['filename']);
                Table::sqlExec("UPDATE DOKU_FILES SET NOTES='".Str::escape($ajax_result['text'])."' WHERE ID = ".$_ARGS['id']);
            }
            if ($_ARGS['ext']=='txt'){
                $ajax_result['filename'] = DOKUX_FILES_DIR.'/'.$row['FILE_NAME'];
                $ajax_result['text'] = Dokux::getTXTFile($ajax_result['filename']);
                Table::sqlExec("UPDATE DOKU_FILES SET NOTES='".Str::escape($ajax_result['text'])."' WHERE ID = ".$_ARGS['id']);
            }
            //  $ajax_result['hash']=hash('crc32',$hash);
        }
        //$ajax_result['msg'] = '(('.$row['VALIDATED'].'))'; //'Este documento está validado. No se puede modificar';
        echo json_encode($ajax_result);



    }else if($_ARGS['op']=='note'){

        // convert 243541.pdf -fill red -undercolor '#ffff0060' -pointsize 10 -gravity North -annotate +10+5 ' Se vende Opel Corsa. Siempre en garage.No por mucho tempranar amanece mas mendrugo.  ' 243541_nota.pdf


        $file_name = DOKUX_INBOX_DIR.'/'.$_ARGS['file'];
        $note_text = $_ARGS['text_note'];
      //$cmd_convert = 'convert '.$file_name.' -fill red -undercolor \'#ffff0060\' -pointsize 10 -gravity North -annotate +10+5 \' '.$note_text.'  \' '.$file_name;
        $cmd_convert = 'convert '.$file_name.' -fill red -undercolor yellow -pointsize 10 -gravity North -annotate +10+5 \' '.$note_text.'  \' '.$file_name;
        $ajax_result['cmd_convert']=$cmd_convert;
        $cmd_output = shell_exec($cmd_convert);
 
    }else if($_ARGS['op']=='ocr'){
       // session_write_close ( ); 

       if($_ARGS['ext']=='pdf'){
           $file_name = DOKUX_INBOX_DIR.'/'.$_ARGS['file'];
           $file_name_out = DOKUX_INBOX_OCR.'/'.$_ARGS['file'];
         //$cmd_convert = 'convert -density 600 -quality 100 -depth 8 -alpha remove -background white -flatten -colorspace GRAY '.$file_name.'.pdf -append '.$file_name_out.OCR_IMG_EXT;  //-combine
           $cmd_convert = 'convert -density 300 -quality 100 -depth 8 -alpha remove -background white -colorspace GRAY '.$file_name.'.pdf -append '.$file_name_out.OCR_IMG_EXT;  //-combine


           /*
           convert -density 600 -quality 100 -depth 8 -alpha remove -background white -flatten -colorspace GRAY /home/APP_NAME/jts27n/hlauniflow_gestion_3445_001zz.pdf -append /home/APP_NAME/jts27n/ocr/hlauniflow_gestion_3445_001zz.jpg
           convert-im6.q16: cache resources exhausted `/home/APP_NAME/jts27n/hlauniflow_gestion_3445_001zz.pdf' @ error/cache.c/OpenPixelCache/4083.
           convert-im6.q16: width or height exceeds limit `/home/APP_NAME/jts27n/hlauniflow_gestion_3445_001zz.pdf' @ error/cache.c/OpenPixelCache/3912.
           convert-im6.q16: cache resources exhausted `/home/APP_NAME/jts27n/hlauniflow_gestion_3445_001zz.pdf' @ error/cache.c/OpenPixelCache/4083.
           */


           $ajax_result['cmd_convert']=$cmd_convert;
           $cmd_output = shell_exec($cmd_convert);
           $ajax_result['output'] = $cmd_output; 
           if (file_exists($file_name_out.OCR_IMG_EXT)){  //FIX check filesize
               $cmd_ocr     = 'tesseract  '.$file_name_out.OCR_IMG_EXT.' '.$file_name_out.' -l spa';
               $cmd_output = shell_exec($cmd_ocr);
               if (file_exists($file_name_out.'.txt')){
                   $ajax_result['text'] = Dokux::read_file_text( $file_name_out.'.txt'); // file_get_contents( $file_name.'.txt');
                   $ajax_result['msg'] = 'SUCCESS'; 
               }else{
                   $ajax_result['error'] = 1; 
                   $ajax_result['msg'] = 'ERROR No existe el txt'; 
               }
           }else if (file_exists($file_name_out.'-0'.OCR_IMG_EXT)){
               $page_index = 0;
               $max_pages=25;
               $file_name_index = $file_name_out.'-'.$page_index;
               while(file_exists( $file_name_index.OCR_IMG_EXT)){
                   $page_index++;  
                   if($page_index>$max_pages) break;
                   $cmd_ocr     = 'tesseract  '.$file_name_index.OCR_IMG_EXT.' '.$file_name_index.' -l spa';
                   $cmd_output = shell_exec($cmd_ocr);
                   if (file_exists($file_name_index.'.txt')){
                       $ajax_result['text'] = Dokux::read_file_text( $file_name_index.'.txt'); // file_get_contents( $file_name.'.txt');
                       $ajax_result['msg'] = 'SUCCESS'; 
                   }else{
                       $ajax_result['error'] = 1; 
                       $ajax_result['msg'] = 'ERROR No existe el txt'; 
                   }
                   $file_name_index = $file_name_out.'-'.$page_index;
               }
           }else{
               $ajax_result['error'] = 1; 
               $ajax_result['msg'] = 'ERROR No se pudo crear el archivo '.$file_name_out.OCR_IMG_EXT; 
           }
       }else if($_ARGS['ext']=='jpg' || $_ARGS['ext']=='webp'){
           $file_name = DOKUX_INBOX_DIR.'/'.$_ARGS['file'];
           $file_name_out = DOKUX_INBOX_OCR.'/'.$_ARGS['file'];
           $cmd_ocr     = 'tesseract  '.$file_name.OCR_IMG_EXT.' '.$file_name_out;
           $cmd_output = shell_exec($cmd_ocr);
           if (file_exists($file_name_out.'.txt')){
               $ajax_result['text'] = Dokux::read_file_text( $file_name_out.'.txt'); // file_get_contents( $file_name.'.txt');
               $ajax_result['msg'] = 'SUCCESS'; 
           }else{
               $ajax_result['error'] = 1; 
               $ajax_result['msg'] = 'ERROR No existe el txt'; 
           }
       }else if($_ARGS['ext']=='zip'){

           $zipfile = DOKUX_INBOX_DIR.'/'.$_ARGS['file'].'.zip';
           $file_name_out = DOKUX_INBOX_DIR.'/'.$_ARGS['file'].'.txt';
           $ajax_result['text'] = Dokux::getZipFilelist($zipfile,$file_name_out);

       }else if($_ARGS['ext']=='mp3'){

           $mp3file              = DOKUX_INBOX_DIR.'/'.$_ARGS['file'].'.mp3';
           $file_name_out        = DOKUX_INBOX_DIR.'/'.$_ARGS['file'].'.txt';
           $thumb_file           = DOKUX_FILES_TMB.'/'.sha1_file($mp3file).'.jpg';
           $ajax_result['text']  = Dokux::getMp3FileInfo($mp3file,$file_name_out);
           $ajax_result['thumb'] = file_exists($thumb_file) ? $thumb_file : 'no thumb';
       }

       echo json_encode($ajax_result);

    }else if($_ARGS['op']=='fileadd'){

        $filename = Str::sanitizeName(str_replace(' (COPIA)','',$_ARGS['file']),true);
        $fromfile = DOKUX_INBOX_DIR.'/'.$_ARGS['file'];
        $ext = Str::get_file_extension($fromfile);
        $hash = sha1_file($fromfile);
        $tofile = DOKUX_FILES_DIR.'/'.$hash.'.'.$ext; //$filename;
        $ocrfile = DOKUX_INBOX_OCR.'/'.$_ARGS['basename'];
        $ocrtext = '';

        $row = Table::getFieldsValues("SELECT ID,TYPE,FILE_NAME,HASH FROM DOKU_FILES WHERE HASH = '{$hash}'");
        if($row){ 

            $ajax_result['error'] = 1;
            $ajax_result['msg'] = 'Ya existe un documento idéntico este: '.$row['FILE_NAME'];

        }else{

            rename( $fromfile, $tofile );

            // Buscar TXT en INBOX/OCR para añadir en notes (pdf_text)
            if (file_exists($ocrfile.'.txt')){ 

                $ocrtext  .= Dokux::read_file_text( $ocrfile.'.txt' );

                // unlink($ocrfile.'.txt');
                // unlink($jpgfile.OCR_IMG_EXT);

            }elseif (file_exists($ocrfile.'-0.txt')){
               
               $page_index = 0;
               $max_pages=25;
               $file_name_index = $ocrfile.'-0';
               while(file_exists( $file_name_index.'.txt')){
                   $page_index++;  
                   if($page_index>$max_pages) break;
                   $ocrtext .= Dokux::read_file_text( $file_name_index.'.txt' );
                   //unlink($file_name_index.'.txt');
                   // unlink($file_name_index.OCR_IMG_EXT);
                   $file_name_index = $ocrfile.'-'.$page_index;
               }
               
            }
            $ocrtext = Dokux::escape($ocrtext);
            $ocrtext_wsw = Str::removeStopWords($ocrtext);
            $doc_type   = Dokux::text2doctype($ocrtext_wsw);
            $identifier = Dokux::text2identifier($ocrtext_wsw);

            if (file_exists($tofile)){
               if(trim($ocrtext)==''){
                $ajax_result['error'] = 1;
                $ajax_result['msg'] = 'No se puede leer el texto de  '.$file_name_index.'.txt';
               }else{
                   //$ajax_result['id'] = Table::nextInsertId('DOKU_FILES');
                   //$ajax_result['sql'] ="INSERT INTO DOKU_FILES (DATE,FILE_NAME,USER_ID,NOTES) VALUES(CURDATE(),'". $filename."',".$_SESSION['userid'].",'".Str::escape($ocrtext)."')";
                   $sql = "INSERT INTO DOKU_FILES (DATE,SERVICIO,IDENTIFIER,NAME,FILE_NAME,USER_ID,TYPE,NOTES,HASH) "
                        . "VALUES(CURDATE(), ".($_SESSION['DOKU_AREA']['ID']?$_SESSION['DOKU_AREA']['ID']:'\'0\'')." ,'". $identifier."','". $filename."','". $hash.'.'.$ext."',".$_SESSION['userid'].",'{$doc_type['ID']}','".Str::escape($ocrtext)."','".$hash."')";
                   $ajax_result['sql'] = $sql;
                   $ajax_result['ok'] = Table::sqlExec($sql);
                   $ajax_result['error'] = 0;
                   /*************************/
                   if (file_exists($fromfile))  {
                       unlink($fromfile);
                       $ajax_result['msg'] = 'Este ('.$_ARGS['file'].') se resistía.';
                   }
                   /**/
                    
                   if (file_exists($ocrfile.'.txt')){ 
                       unlink($ocrfile.'.txt');
                       unlink($ocrfile.OCR_IMG_EXT);
                   }elseif (file_exists($ocrfile.'-0.txt')){
                       $page_index = 0;
                       $max_pages=25;
                       $file_name_index = $ocrfile.'-0';
                       while(file_exists( $file_name_index.'.txt')){
                           $page_index++;  
                           if($page_index>$max_pages) break;
                           unlink($file_name_index.'.txt');
                           unlink($file_name_index.OCR_IMG_EXT);
                           $file_name_index = $ocrfile.'-'.$page_index;
                       }
                   }
               } 
            }else{
                $ajax_result['error'] = 1;
                $ajax_result['msg'] = 'No existe '.$tofile;
            }
        }
        echo json_encode($ajax_result);

    }else if($_ARGS['op']=='filedelete'){

        $filename = $_ARGS['file']; //Str::sanitizeName(str_replace(' (COPIA)','',$_ARGS['file']),true);

        if (file_exists(DOKUX_INBOX_DIR.'/'.$filename))  {
            unlink( DOKUX_INBOX_DIR.'/'.$filename );
            if (file_exists(DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].OCR_IMG_EXT)){
                unlink( DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].OCR_IMG_EXT);
            }elseif (file_exists(DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'-0'.OCR_IMG_EXT)){
               $page_index = 0;
               $max_pages=25;
               $file_name_index = DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'-0'.OCR_IMG_EXT;
               while(file_exists( $file_name_index)){
                   $page_index++;  
                   if($page_index>$max_pages) break;
                   unlink( $file_name_index );
                   $file_name_index = DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'-'.$page_index.OCR_IMG_EXT;
               }
            }

            if (file_exists(DOKUX_FILES_TMB.'/'.$_ARGS['basename'].'.jpg'))  unlink( DOKUX_FILES_TMB.'/'.$_ARGS['basename'].'.jpg');

            if (file_exists(DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'.txt')){ 
                unlink( DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'.txt');
            }elseif (file_exists(DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'-0.txt')){
               $page_index = 0;
               $max_pages=25;
               $file_name_index = DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'-0.txt';
               while(file_exists( $file_name_index)){
                   $page_index++;  
                   if($page_index>$max_pages) break;
                   unlink( $file_name_index );
                   $file_name_index = DOKUX_INBOX_OCR.'/'.$_ARGS['basename'].'-'.$page_index.'.txt';
               }
            }

        }else{
            $ajax_result['error'] = 1;
        }

        echo json_encode($ajax_result);
    
    }else if($_ARGS['op']=='getusers'){

        $ajax_result['users'] =  AreasACL::getUsersWithPermission('fac',APP_NAME,'doku_view');
        $ajax_result['opers'] =  AreasACL::getUsersWithPermission('fac',APP_NAME,'doku_add');
        $ajax_result['admins'] = AreasACL::getUsersWithPermission('fac',APP_NAME,'doku_admin');

        // ahora Array ( [0] => Array ( [ID] => 1 [USERNAME] => jts27n [SOURCE] => area_admin ) [1] => Array ( [ID] => 17 [USERNAME] => drakar73@msn.com [SOURCE] => group_derived ) )
         //antes: $users[$v2['id_user']] = $this->getUsername($v2['id_user'],$full);

        echo json_encode($ajax_result);
    
    }else if($_ARGS['op']=='upload'){
        /**
            Array
            (
                [FILE_NAME] => Array
                    (
                        [name] => julian_550_2.jpg
                        [type] => image/jpeg
                        [tmp_name] => /tmp/phpCiVBVX
                        [error] => 0
                        [size] => 59848
                    )

            )
        */
        if(isset($_FILES['FILE_NAME'])){
            $filename =   Str::sanitizeName(str_replace(array('',' (COPIA)'),array('_',''),   $_FILES['FILE_NAME']['name']),true);
            if(move_uploaded_file($_FILES['FILE_NAME']['tmp_name'], DOKUX_INBOX_DIR . '/' . $filename)){
                $ocr = Dokux::gettext($filename);
                $ajax_result['msg'] = print_r($_FILES,true);
                $ajax_result['replaced'] = false;
                $ajax_result['local_file'] = /**DOKUX_INBOX_DIR . '/' . */$filename;
            }else{
                $ajax_result['error'] = 1;
                $ajax_result['msg'] = 'Error al guardar el archivo '.$filename;
            }
        }else{
            $ajax_result['error'] = 1;
            $ajax_result['msg'] = 'No existe el archivo '.$filename;
        }
        //sleep(3);
        echo json_encode($ajax_result);

        /********************/

    }else{

        include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');

    }
