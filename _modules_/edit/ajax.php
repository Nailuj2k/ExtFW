<?php
     
    if ( $_ACL->hasPermission('site_edit') == false) die('illegal access');

    //include(SCRIPT_DIR_MODULE.'/edit_ware.class.php');
    //include(SCRIPT_DIR_CLASSES.'/sys.class.php');

    $ajax_result = array();
    $ajax_result['error']=0;

    if       ($_ARGS['op']=='getfile'){

        $_ARGS['file'] = $_ARGS['file']=='htaccess' ? '.htaccess' : $_ARGS['file'];

        $file = /*SCRIPT_DIR_MODULE.'/'.*/$_ARGS['file']; //.'.'.$_ARGS['ext'];

        if (file_exists($file)){

            $path     = pathinfo($file);
            $basename = $path['basename'];
            $id       = md5($file);
            $basename = $path['filename'];
            $ext      = $path['extension'];
            $key      = hash('crc32',$basename);
            $size     = filesize($file);

            $ajax_result['filename'] = $filename.' exists!';
            if(in_array($ext,['php','css','html','js','txt','htaccess','json','sql','lock','py','md','log'])){
                $ajax_result['type'] = 'code'; 
                $ajax_result['text'] = Crypt::str2crypt(EDIT_ware::read_file_text( $file ),$_SESSION['token']); //  file_get_contents( $filename); 
            }else if(in_array($ext,['jpg','gif','png','webp','svg'])){
                $ajax_result['type'] = 'image'; 
            }else if(in_array($ext,['ttf','eot','otf'])){
                $ajax_result['type'] = 'font'; 
            }else if(in_array($ext,['zip','rar','7z'])){
                $ajax_result['type'] = 'zip'; 
                $ajax_result['text'] = Crypt::str2crypt(EDIT_ware::read_file_zip( $file ),$_SESSION['token']);            
            }else if(in_array($ext,['gz'])){
                $ajax_result['type'] = 'zip'; 
                $ajax_result['text'] = Crypt::str2crypt(EDIT_ware::read_file_gz( $file ),$_SESSION['token']);            
            }else if(in_array($ext,['csv'])){
                $ajax_result['type'] = 'csv'; 
                $ajax_result['text'] = Crypt::str2crypt(EDIT_ware::read_file_csv( $file ),$_SESSION['token']);            
            }

            $ajax_result['ext'] = $ext; 
            $ajax_result['msg'] = 'SUCCESS'; 
        }else{
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR No existe el archivo'; 
        }

    } else if($_ARGS['op']=='movefile'){

           // $ajax_result['msg'] = 'SUCCESS'.'<br>'.$_ARGS['from_dir'].'<br>'.$_ARGS['to_dir'].'<br>'.$_ARGS['filename']; 

            // Construir las rutas completas de origen y destino
            $from_path = rtrim($_ARGS['from_dir'], '/') . '/' . $_ARGS['filename'];
            $to_path = rtrim($_ARGS['to_dir'], '/') . '/' . $_ARGS['filename'];

            // Verificar si el archivo de origen existe
            if (!file_exists($from_path)) {
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] = "Error: El archivo '{$_ARGS['filename']}' no existe en el directorio de origen.";
            }

            // Verificar si el directorio de destino existe
            if (!is_dir($to_path)) {
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] = "Error: El directorio de destino {$to_path} no existe.";
            }

            // Intentar mover el archivo
            if (rename($from_path, $to_path)) {
                $ajax_result['error'] = 0; 
                $ajax_result['msg'] = "El archivo '{$_ARGS['filename']}' se movió correctamente a {$to_path}";
            } else {
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] =  "Error: No se pudo mover el archivo '{$_ARGS['filename']}'.";
            }

    } else if($_ARGS['op']=='deletefile'){

        $filename =/* $_SERVER['DOCUMENT_ROOT'].*/$_POST['filename'] ?? '';

        //$filename = $_SERVER['DOCUMENT_ROOT'].'/_js_/image_editor/demo/1733768353.jpg';
        
        if (file_exists($filename)){
           
            if (stristr($filename,'_bak_')===false){
                if (stristr($filename,SCRIPT_DIR_MODULES)){
                    $basename = pathinfo($filename, PATHINFO_BASENAME);
                    if(in_array($basename,['init.php','index.php','style.css','ajax,php','run.php','head.php','script.js','footer.php','pdf.php'])){
                        $ajax_result['error'] = 1; 
                        $ajax_result['msg'] = 'WARNING no se permite eliminar el archivo '.$filename;                       
                    }
                }
                if (stristr($filename,SCRIPT_DIR_INCLUDES) || stristr($filename,SCRIPT_DIR_CLASSES )){
                    $ajax_result['error'] = 1; 
                    $ajax_result['msg'] = 'WARNING no se permite eliminar el archivo '.$filename;                       
                }
            }
            if ($ajax_result['error'] === 0){
                if (stristr($filename,'_bak_')===false){
                    $bak_dir = str_replace( $_SERVER['DOCUMENT_ROOT'], $_SERVER['DOCUMENT_ROOT'].'/_bak_/'.date('YmdHi'), dirname($filename));
                    SYS::mkdirr($bak_dir);
                    if(copy($filename, $bak_dir.'/'.basename($filename) )) $result['bak']='OK'; else $result['bak']='KO';
                }
                if (unlink($filename)){
                    $ajax_result['msg'] = 'SUCCESS El archivo '.$filename.' ha sido eliminado';   
                }else{
                    $ajax_result['error'] = 1; 
                    $ajax_result['msg'] = 'ERROR al eliminar el archivo '.$filename;                   
                }
            }
        }else{
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR:: No existe el archivo ['.$filename.']';   //    [/_js_/image_editor/demo/1729179352.jpg]
        }

    } else if($_ARGS['op']=='renamefile'){
        // { 'op': 'renamefile', 'filename':finalFilename,'content':'' },        

        $filename = $_POST['filename'] ?? '';
        $newname  = $_POST['newname']  ?? '';
        $old_ext = pathinfo($filename, PATHINFO_EXTENSION);
        $new_ext = pathinfo($newname, PATHINFO_EXTENSION);
        
        if (!$filename) {
            //throw new Exception('Falta el nombre del archivo');
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Falta el nombre del archivo';   
            
        }else if (empty($new_ext)){
            
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Falta la extensión';   

        }else if (!in_array($new_ext,['php','css','html','js','txt','htaccess','json','sql','lock','py','md','zip','csv','jpg','gif','webp','png','svg','ttf','eot','otf','rar','7z'])){
            
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Extensión de archivo no permitida';   
        
        }else if (($old_ext!='php' && $new_ext=='php')||($old_ext!='py' && $new_ext=='py')){
            
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Cambio de extensión no permitido';   
            
        }else{
            
            $old = pathinfo($filename, PATHINFO_BASENAME);
            $new = pathinfo($newname,  PATHINFO_BASENAME);

            if (file_exists($newname)){
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] = 'ERROR Ya existe un archivo con el nombre '.$new;   
            }else{
                $ret = rename($filename, $newname);
                $ajax_result['id']    = md5($newname);
                $ajax_result['error'] = $ret === false ? 1 : 0 ;
                $ajax_result['msg']   = $ret === false ? 'Error al renombrar el archivo '.$old  : 'Archivo '.$old.' renombrado a '.$new ;
            }

        }

    } else if($_ARGS['op']=='newfile'){

        // { 'op': 'newfile', 'filename':finalFilename,'content':'' },        

      //$file     = Str::sanitizeName($_POST['filename'],true);
        $file     = $_POST['filename'] ?? '';
        $content  = $_POST['content']  ?? "\n";
        $ext      = pathinfo($file, PATHINFO_EXTENSION);

        if (!$file) {
            
            //throw new Exception('Falta el nombre del archivo');
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Falta el nombre del archivo';   
            
        }else if (!in_array($ext,['php','css','html','js','txt','htaccess','json','sql','lock','py','md'])){
            
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Extensión de archivo no permitida';   
            
        }else{
            
            
            switch ($ext) {
                case  'php': $ok = SYS::create_file_php ($file, $content );break;
                case  'css': $ok = SYS::create_file_css ($file, $content );break;
                case 'html': $ok = SYS::create_file_html($file, $content );break;
                case   'js': $ok = SYS::create_file_js  ($file, $content );break;
                default: $ok = SYS::create_file     ($file, $content );
            }
            
            if ( $ok ){
                $ajax_result['file'] = $file; 
                $ajax_result['id']   = md5($file);
                $ajax_result['parent']   = md5(pathinfo($file, PATHINFO_DIRNAME));
                $ajax_result['size'] = filesize($file);
                $ajax_result['path'] = pathinfo($file, PATHINFO_DIRNAME); 
                $ajax_result['basename'] = pathinfo($file, PATHINFO_BASENAME); 
                $ajax_result['filename'] = pathinfo($file, PATHINFO_FILENAME); 
                $ajax_result['ext'] = $ext; 
                $ajax_result['msg'] = 'New file: '.$file; 
            }else{
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] = 'Error al guardar el archivo';
            }

        }

    } else if($_ARGS['op']=='newfolder'){
        // { 'op': 'newfile', 'filename':finalFilename,'content':'' },        

        //$folder     = Str::sanitizeName($_POST['foldername']);
        $folder     = $_POST['foldername'] ?? '';

        if (!$folder) {
            
            //throw new Exception('Falta el nombre del archivo');
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Falta el nombre del archivo';   
                        
        }else{
            
            $ok = mkdir($folder);

            if ( $ok ){
                $ajax_result['file'] = $folder; 
                $ajax_result['id']   = md5($folder);
                $ajax_result['parent']   = md5(pathinfo($folder, PATHINFO_DIRNAME));
                $ajax_result['size'] = 0;
                $ajax_result['path'] = pathinfo($folder, PATHINFO_DIRNAME); 
                $ajax_result['basename'] = pathinfo($folder, PATHINFO_BASENAME); 
                $ajax_result['filename'] = pathinfo($folder, PATHINFO_FILENAME); 
                $ajax_result['ext'] = 'DIR'; 
                $ajax_result['msg'] = 'New folder: '.$folder; 
            }else{
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] = 'Error al crear la carpeta: '.$folder;
            }

        }

    } else if($_ARGS['op']=='renamedir'){

        $filename = $_POST['filename'] ?? '';
        $newname  = $_POST['newname']  ?? '';
        
        if (!$filename) {
            //throw new Exception('Falta el nombre del archivo');
            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR Falta el nombre del directorio';   
                        
        }else{
            
            $old = pathinfo($filename, PATHINFO_BASENAME);
            $new = pathinfo($newname,  PATHINFO_BASENAME);

            if (file_exists($newname)){
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] = 'ERROR Ya existe un directorio con el nombre '.$new;   
            }else{
                $ret = rename($filename, $newname);
                $ajax_result['id']    = md5($newname);
                $ajax_result['error'] = $ret === false ? 1 : 0 ;
                $ajax_result['msg']   = $ret === false ? 'Error al renombrar el directorio '.$old  : 'Directorio '.$old.' renombrado a '.$new ;
            }

        }

    } else if($_ARGS['op']=='deletedir'){

        $filename = $_POST['filename'] ?? '';
        if (file_exists($filename)){
             if (stristr($filename,SCRIPT_DIR_INCLUDES) 
              || stristr($filename,SCRIPT_DIR_CLASSES )
              || stristr($filename,SCRIPT_DIR_MODULES )
              || stristr($filename,SCRIPT_DIR_LIB )
              || stristr($filename,SCRIPT_DIR_JS )
              || stristr($filename,SCRIPT_DIR_MEDIA )
              || stristr($filename,SCRIPT_DIR_OUTPUTS )
              || stristr($filename,SCRIPT_DIR_THEMES )){
                $ajax_result['error'] = 1; 
                $ajax_result['msg'] = 'WARNING no se permite eliminar el directorio '.$filename;                       
            }
            if ($ajax_result['error'] === 0){
                if (rmdir($filename)){
                    $ajax_result['msg'] = 'SUCCESS El directorio '.$filename.' ha sido eliminado';   
                }else{
                    $ajax_result['error'] = 1; 
                    $ajax_result['msg'] = 'ERROR al eliminar el archivo '.$filename;                   
                }
            }
        }else{

            $ajax_result['error'] = 1; 
            $ajax_result['msg'] = 'ERROR No existe el directorio '.$filename;   
        
        }

    } else if($_ARGS['op']=='settext_TEST'){

        //$_ARGS['text']
        $ret = false;
        $ajax_result['error'] = $ret === false ? 1 : 0 ;
        $ajax_result['msg']   = $ret === false ? 'Error al guardar el archivo '.$_ARGS['file']  : 'Archivo '.$_ARGS['file'].' guardado' ;
        $ajax_result['id']    = $_ARGS['id'];  
        $ajax_result['file']  = $_ARGS['file'];


        if (isset($_FILES['text']) && $_FILES['text']['error'] === UPLOAD_ERR_OK) {
            // Leer el contenido del Blob como archivo
            $text = file_get_contents($_FILES['text']['tmp_name']);
            // Aquí tienes el texto encriptado en $text
            $ajax_result['text']  = $text;
        }else{
            $ajax_result['text']  = 'NULL';
        }

    } else if($_ARGS['op']=='settext'){

        $_ARGS['file'] = $_ARGS['file']=='htaccess' ? '.htaccess' : $_ARGS['file'];

        if (file_exists( $_ARGS['file'] ) ){
           if (is_writable (  $_ARGS['file'] ) ){


                if (isset($_FILES['text']) && $_FILES['text']['error'] === UPLOAD_ERR_OK) {
                    $_encrypted_text = file_get_contents($_FILES['text']['tmp_name']);
                    $_decrypted_text = Crypt::crypt2str($_encrypted_text,$_SESSION['token']);
                }else{
                    $_encrypted_text = '';
                    $_decrypted_text = '';
                }   

                if((!$_decrypted_text || $_decrypted_text=='' ) && strlen($_encrypted_text)>3){

                    $text = NULL;
                    $ajax_result['error'] = 1;
                    $ajax_result['msg'] = t('TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION'); 

                }else{

                    $text = $_decrypted_text;

                    $bak_file = str_replace( $_SERVER['DOCUMENT_ROOT'], $_SERVER['DOCUMENT_ROOT'].'/_bak_/'.date('YmdHi'), $_ARGS['file']);
                    if ( $_ARGS['file']=='.htaccess') $bak_file = $_SERVER['DOCUMENT_ROOT'].'/_bak_/'.date('YmdHi').'/htaccess.txt';

                    $bak_dir = dirname($bak_file);

                    SYS::mkdirr($bak_dir);

                    if ( copy($_ARGS['file'], $bak_file ) ) {

                        $ret = file_put_contents($_ARGS['file'],$text);

                        $ajax_result['error'] = $ret === false ? 1 : 0 ;
                        $ajax_result['msg']   = $ret === false ? 'Error al guardar el archivo '.$_ARGS['file']  : 'Archivo '.$_ARGS['file'].' guardado' ;
                        $ajax_result['id']    = $_ARGS['id'];  
                        $ajax_result['file']  = $_ARGS['file'];  
                        //$ajax_result['text']  = $_ARGS['text']; 
 
                    }else{
                        $ajax_result['error'] = 1 ;
                        $ajax_result['msg']    = 'No se pudo guardar copia de seguridad de el archivo '.$_ARGS['file'].' en '.$bak_file;  
                    }
                 
                }
                 
            }else{
                $ajax_result['error'] = 1 ;
                $ajax_result['msg']    = 'No tiene permisos de escritura sobre el archivo '.$_ARGS['file'];  
            } 
        }else{
            $ajax_result['error'] = 1 ;
            $ajax_result['msg']    = 'No existe el archivo '.$_ARGS['file'];  
        } 

    } else if($_ARGS['op']=='search'){
        $query = trim($_ARGS['query']);
        $dir = $_ARGS['rootdir'] ?? '_modules_';//$_SERVER['DOCUMENT_ROOT'];

        $parseDir = EDIT_ware::search($dir, $query, true);
        $ajax_result['files'] = $parseDir['files'];
        $ajax_result['matches'] = $parseDir['matches'];
        $ajax_result['query'] = $query;
        $ajax_result['dir'] = $dir;
        $ajax_result['msg'] = 'SUCCESS';

    } else if($_ARGS['op']=='dir'){
        sleep(1);
        $dir = $_ARGS['rootdir'];
        //FIX check if dir exists, if have permission, if is a dir, if is a module || include || class dir, etc
        //$root_dir = $_SERVER['DOCUMENT_ROOT'].'/'.$dir;

        $parseDir = EDIT_ware::parseDir($dir,true);
        $ajax_result['files'] = $parseDir['files'];
        $ajax_result['dir'] = $dir; 
        $ajax_result['parent'] = md5($dir);
        $ajax_result['msg'] = 'SUCCESS'; 

    } else if($_ARGS['op']=='ai'){


        define('DEEPSEEK_API_KEY', CFG::$vars['ai']['deepseek']['api_key']);
        define('OPENAI_API_KEY'  , CFG::$vars['ai']['openai'  ]['api_key']);
        define('CLAUDE_API_KEY'  , CFG::$vars['ai']['claude'  ]['api_key']); // sustituir por la clave real
        define('GEMINI_API_KEY'  , CFG::$vars['ai']['gemini'  ]['api_key']);
        define('GROK_API_KEY'    , CFG::$vars['ai']['grok'    ]['api_key']);
        define('KIMI_API_KEY'    , CFG::$vars['ai']['kimi'    ]['api_key']);
        define('OLLAMA_HOST'    , CFG::$vars['ai']['ollama'  ]['host'] ?? 'http://localhost:11434');
        define('OLLAMA_MODEL'   , CFG::$vars['ai']['ollama'  ]['model'] ?? 'gpt-oss:20b-cloud'); 
        define('OLLAMA_API_KEY' , CFG::$vars['ai']['ollama'  ]['api_key'] ?? '052715d1d1e94f6e859b6b3e31a88fe9.1uxCp2CMs78Vlh15CCinrgdI') ;
        // Test calling directly:
        // https://domain.net/edit/ajax/op=ai/service=claude/token=<TOKEN_FROM_SESSION>/question=hello

       include(SCRIPT_DIR_MODULE.'/ai/AiServiceInterface.php');

       try {
           switch( $_ARGS['service'] ){
                case 'dummy'   :
                    include(SCRIPT_DIR_MODULE.'/ai/DummyAiService.php');
                    $aiService = new \AIServices\DummyAiService();
                    break;
                case 'deepseek':
                    include(SCRIPT_DIR_MODULE.'/ai/DeepSeekAiService.php');
                    $aiService = new \AIServices\DeepSeekAiService();
                    break;
                case 'openai'  :
                    include(SCRIPT_DIR_MODULE.'/ai/OpenAiService.php');
                    $aiService = new \AIServices\OpenAiService();
                    break;
                case 'claude'  :
                    include(SCRIPT_DIR_MODULE.'/ai/ClaudeAiService.php');
                    $aiService = new \AIServices\ClaudeAiService();
                    break;
                case 'gemini'  :
                    include(SCRIPT_DIR_MODULE.'/ai/GeminiAiService.php');
                    $aiService = new \AIServices\GeminiAiService();
                    break;
                case 'grok'    :
                    include(SCRIPT_DIR_MODULE.'/ai/GrokAiService.php');
                    $aiService = new \AIServices\GrokAiService();
                    break;
                case 'ollama'  :
                    include(SCRIPT_DIR_MODULE.'/ai/OllamaAiService.php');
                    $aiService = new \AIServices\OllamaAiService();
                    break;
                case 'kimi'    :
                    include(SCRIPT_DIR_MODULE.'/ai/KimiAiService.php');
                    $aiService = new \AIServices\KimiAiService();
                    break;
                default        :
                    include(SCRIPT_DIR_MODULE.'/ai/DummyAiService.php');
                    $aiService = new \AIServices\DummyAiService();
            }

            $question = $_ARGS['question'] ?? '';
            // Decode base64 if encoded flag is set (to bypass WAF blocking PHP code)
            if (!empty($_ARGS['encoded'])) {
                $question = base64_decode($question);
            }
            $answer = $aiService->askQuestion($question);
            $ajax_result['answer'] = $answer;
            $ajax_result['msg'] = 'SUCCESS';

       } catch (Exception $e) {
           $ajax_result['error'] = true;
           $ajax_result['answer'] = 'Error: ' . $e->getMessage();
           $ajax_result['msg'] = 'AI_ERROR';
       }

    }         

    if($_ARGS['test']){

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ALL); // ^ E_NOTICE ); // 1 E_ALL);

        $json = json_encode($ajax_result, JSON_INVALID_UTF8_SUBSTITUTE);   

        if ($json === false) {
            echo 'JSON Error: ' . json_last_error_msg();
            echo '<pre>';
            print_r($ajax_result);
            echo '</pre>';
            exit;
        }

        echo $json; //$ajax_result['files']);
    }else
        echo json_encode($ajax_result, JSON_INVALID_UTF8_SUBSTITUTE);   
