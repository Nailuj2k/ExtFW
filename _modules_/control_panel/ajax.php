<?php


//   
// find ./public -type f -exec du -h {} + | sort -rh | head -n 100
    
//    $version = explode('.', PHP_VERSION);
//    if ($version[0] >= '7'){
//       if(file_exists('./vendor/autoload.php')) 
//            include('INCLUDE_composer.php');
//    }

    if (!$_SESSION['userid'] || $_SESSION['userid']>20 ) 
        if (isset($_ARGS[3])||($_POST))
            if ($_ARGS['2']!='op=list')
                log2mail($_ARGS,'_ARGS');

    if       ( Root()          && $_ARGS['op']=='save_cfg'        ){

        $result = array();
        
        $result['error'] = 0;
        //$result['msg']   = 'datos recibidos: '.print_r($_ARGS['themes'],true);      

        $configuration_content='<'.'?php

            /* * * *
             * 
             * ExtFW '. CFG::$vars['script']['version'] . '
             *
             *
             * */
             
            $cfg[\'prefix\']           = \''.$_ARGS['prefix'].'\';
            $cfg[\'debug\']            = false;
            $cfg[\'timezone\']         = \'Europe/Madrid\';  // Valid timezones list: http://php.net/manual/timezones.php
            $cfg[\'db\'][\'type\']     = \''.$_ARGS['dbtype'].'\';
            $cfg[\'db\'][\'host\']     = \''.$_ARGS['dbhost'].'\';
            $cfg[\'db\'][\'user\']     = \''.$_ARGS['dbuser'].'\';
            $cfg[\'db\'][\'pass\']     = \''.$_ARGS['dbpass'].'\';    
            $cfg[\'db\'][\'name\']     = \''.$_ARGS['dbname'].'\';  
            $cfg[\'db\'][\'cache\']    = '.($_ARGS['dbcache']?'true':'false').';
            $cfg[\'db\'][\'log\']      = '.($_ARGS['dblog']  ?'true':'false').';
            $cfg[\'repo\'][\'url\']    = \''.CFG::$vars['repo']['url'].'\';  
            $cfg[\'auth\']             = \''.$_ARGS['auth'].'\';      //\'sqlite\' ,\'mysql\' \'demo\', \'internal\';  // \'ldap\'  '.($_ARGS['redis']?'   
            $cfg[\'redis\']            = '.($_ARGS['redis']?'true':'false').';
            $cfg[\'redis_dsn\']        = \''.$_ARGS['redis_dsn'].'\';                                                               ':'').'
            $cfg[\'ldap_server\']      = '.($_ARGS['ldap_server']?'\''.$_ARGS['ldap_server'].'\'':'false').'; 
            $cfg[\'ldap_port\']        = '.($_ARGS['ldap_port']?'\''.$_ARGS['ldap_port'].'\'':'false').'; 
            $cfg[\'ldap_context\']     = '.($_ARGS['ldap_context']?'\''.$_ARGS['ldap_context'].'\'':'false').'; 
            $cfg[\'ldap_user\']        = '.($_ARGS['ldap_user']?'\''.$_ARGS['ldap_user'].'\'':'false').'; 
            $cfg[\'ldap_password\']    = '.($_ARGS['ldap_password']?'\''.$_ARGS['ldap_password'].'\'':'false').'; 
            $cfg[\'ldap_bind_rdn\']    = '.($_ARGS['ldap_bind_rdn']?'\''.$_ARGS['ldap_bind_rdn'].'\'':'false').'; 
            $cfg[\'ldap_group_rdn\']   = '.($_ARGS['ldap_group_rdn']?'\''.$_ARGS['ldap_group_rdn'].'\'':'false').'; 
            $cfg[\'ldap_group_admin\'] = '.($_ARGS['ldap_group_admin']?'\''.$_ARGS['ldap_group_admin'].'\'':'false').'; 
            $cfg[\'ldap_group_prefix\']= '.($_ARGS['ldap_group_prefix']?'\''.$_ARGS['ldap_group_prefix'].'\'':'false').'; 
            $cfg[\'production\']       = '.($_ARGS['production']?'true':'false').';
            $cfg[\'log\']              = '.($_ARGS['log']?'true':'false').';
            $cfg[\'enable_themes\']    = '.($_ARGS['enable_themes']?'true':'false').';
            $cfg[\'themes\']           = [\''.str_replace(',',"','",$_ARGS['themes']).'\'];
            $cfg[\'default_theme\']    = $cfg[\'themes\']['.$_ARGS['default_theme'].'];
            $cfg[\'enable_langs\']     = '.($_ARGS['enable_langs']?'true':'false').';
            $cfg[\'langs\']            = [\''.str_replace(',',"','",$_ARGS['langs']).'\']; 
            $cfg[\'default_lang\']     = $cfg[\'langs\']['.$_ARGS['default_lang'].'];
            $cfg[\'default_module\']   = \''.$_ARGS['default_module'].'\';   //\'home\';
            $cfg[\'default_page\']     = '.($_ARGS['default_page']?'\''.$_ARGS['default_page'].'\'':'false').';   //\'intro\';
            $cfg[\'module_security\']  = '.($_ARGS['module_security']?'\''.$_ARGS['module_security'].'\'':'false').';  
            $cfg[\'security\'][\'log_attacks\'] = '.($_ARGS['log_attacks']?'true':'false').'; // Log de intentos de ataque
            $cfg[\'security\'][\'phpids_modules\'] =  [\''.str_replace(',',"','",$_ARGS['phpids_modules']).'\'];       // Módulos donde usar PHPIDS              
            '.($_ARGS['master_password'] ?'define(\'MASTER_PASSWORD\'    , \''.$_ARGS['master_password'].'\');':'').'
            define(\'PRIVATE_DIR\'        , '.( dirname($_SERVER['DOCUMENT_ROOT']) == '/var/www' ? 'ROOT_DIR.\'/media\'' : 'dirname(ROOT_DIR)').' ); 
            define(\'MODULE_SHOP\'        , '.(Vars::getArrayVar($_ARGS,'module_shop',false)?'\''.$_ARGS['module_shop'].'\'':'false').');  
            define(\'WYSIWYG_EDITOR\'     , '.($_ARGS['wysiwyg_editor']?'\''.$_ARGS['wysiwyg_editor'].'\'':'false').');  
            define(\'CODE_EDITOR\'        , '.($_ARGS['code_editor']?'\''.$_ARGS['code_editor'].'\'':'monaco').');  
            define(\'CACHE_DIR\'          , '.( !$_ARGS['cache_dir'] || $_ARGS['cache_dir']==PRIVATE_DIR.'/cache' ? 'PRIVATE_DIR.\'/cache\'' : '\''.$_ARGS['cache_dir'].'\'' ).' ); 
            define(\'ERROR_LEVEL\'        , $cfg[\'production\'] ? 0 : E_ALL );
            ';


            //LOG    $cfg[\'log\']              = '.($_ARGS['log']  ?'true':'false').';

        if(defined('SCRIPT_DIR_LOG')) 
            if(!file_exists(SCRIPT_DIR_LOG)) SYS::mkdirr(SCRIPT_DIR_LOG); // create log dir if not exists

        //define(\'MONACO_FROM_CDN\'    , '.(defined('MONACO_FROM_CDN') && MONACO_FROM_CDN==true?'true':'false').');   

        //include(SCRIPT_DIR_CLASSES.'/sys.class.php');
        $filename = 'configuration.php';

        $bak_dir = $_SERVER['DOCUMENT_ROOT'].'/_bak_/'.date('YmdHi');
        SYS::mkdirr($bak_dir);
        if (copy($filename, $bak_dir.'/'.$filename)){
            if($f = fopen($filename,'w+')) {
                if(@fwrite($f,$configuration_content)) { //serialize($data))) {
                    @fclose($f);
                    $result['msg']   = $filename.' actualizado';      
               } else $result['msg']   = "Error::i18n::No se puede escribir en el archivo ".$filename;
            } else $result['msg']   = "Error::i18n::No se puede abrir el archivo ".$filename;
        } else $result['msg']   = "Error::i18n::No se pudo guardar copia de seguridad del archivo ".$filename;
        sleep(2);  // No hay prisa :)
        
        echo json_encode($result);
   
    }else if ( Administrador() && $_ARGS[2]=='log'                ){
        $logFile = $log_filename;  //SCRIPT_DIR_MEDIA.'/log/log_20260105_ext.txt';

        // Seguridad básica: solo usuarios autenticados deberían acceder
        // session_start();
        
        
        header('Content-Type: application/json');
        
        if (!$_SESSION['userid']){
            echo json_encode(['new_content' => 'ACCESS DENIED', 'new_position' => 0]);
            exit;
        }

        if (!file_exists($logFile)) {
            echo json_encode(['new_content' => '', 'new_position' => 0]);
            exit;
        }

        $lastPos = isset($_ARGS['pos']) ? (int)$_ARGS['pos'] : 0;
        clearstatcache();  // Importante para detectar cambios
        $currentSize = filesize($logFile);


        $include_ajax_lines = $_ARGS['ajax_lines']=='yes';

        // Si pos=1 o pos=0, devolver TODO el archivo
        if ($lastPos <= 1) {
            $newContent = file_get_contents($logFile);

            if ($include_ajax_lines !== 'yes' && $newContent !== '') {
                $newContent = preg_replace('/^.*ajax\\/.*(?:\r?\n)?/mi', '', $newContent);
            }

            echo json_encode([
                'new_content' => $newContent,
                'new_position' => $currentSize
            ]);
        } else if ($currentSize > $lastPos) {
            $handle = fopen($logFile, 'r');
            fseek($handle, $lastPos);
            $newContent = fread($handle, $currentSize - $lastPos);
            fclose($handle);

            //if ($include_ajax_lines !== 'yes' && $newContent !== '') {
           //     $newContent = preg_replace('/^.*ajax\\/.*(?:\r?\n)?/mi', '', $newContent);
           // }
                        
            echo json_encode([
                'new_content' => $newContent,
                'new_position' => $currentSize
            ]);
        } else {
            echo json_encode(['new_content' => '', 'new_position' => $lastPos]);
        }


    }else if ( Administrador() && $_ARGS[2]=='logfiles'           ){

        $result = array();
        $result['error']=0;
        $_LOG_DIR  = CACHE_DIR; //FIX $_ARGS['log_dir]  || log_type   

        if($_ARGS['op']=='load')        {
            
            $result['logfiles'] ='';
            foreach (glob($_LOG_DIR.'/*.log') as $path) $docs[$path] = filectime($path); 
            arsort($docs); 
            foreach ($docs as $path => $timestamp) {
                $img_type = '_images_/filetypes/icon_txt.png';
                $filename= basename($path);
                $ext      = Str::get_file_extension($filename);
                $filename = Str::get_file_name($filename);

                $u = MODULE.'/file/filename='.$filename.'/path='.str_replace('/','+',CACHE_DIR).'/name='.$filename.'/ext=log/mode=inline/serialized=1';  
                $result['logfiles'] .= '<span class="file-date">'.date("Y m d H:i", $timestamp).'</span> '
                                    .  '<span class="file-size">'.Str::padl(Str::formatBytes(filesize($path)),8).'</span> &nbsp;'
                                    .  '<a data-href="'.$u.'" class="open_file_json"><!--<img src="'.$img_type.'"> -->'. basename($path) .'</a>'."\n";
            }

        }else if($_ARGS['op']=='load-cache')  {

            $result['cachefiles'] ='';
            foreach (glob($_LOG_DIR.'/*.txt') as $path) $docs[$path] = filectime($path); 
            arsort($docs); 
            foreach ($docs as $path => $timestamp) {
                $img_type = '_images_/filetypes/icon_txt.png';
                $filename= basename($path);
                $ext      = Str::get_file_extension($filename);
                $filename = Str::get_file_name($filename);

                $u = MODULE.'/file/filename='.$filename.'/path='.str_replace('/','+',CACHE_DIR).'/name='.$filename.'/ext=txt/mode=inline/serialized=2'; //   
                $result['cachefiles'] .= '<span class="file-date">'.date("Y m d H:i", $timestamp).'</span> '
                                    .  '<span class="file-size">'.Str::padl(Str::formatBytes(filesize($path)),8).'</span> &nbsp;'
                                    .  '<a data-href="'.$u.'" class="open_file_txt"><!--<img src="'.$img_type.'"> -->'. basename($path) .'</a>'."\n";
            }

        }else if($_ARGS['op']=='delete-log')  {

                foreach (glob($_LOG_DIR.'/*.log') as $filename) {
                    unlink($filename);
                }
                //$_SESSION['token']=false;
                $log->data = array();

        }else if($_ARGS['op']=='delete-cache'){

                foreach (glob($_LOG_DIR.'/cache*.txt') as $filename) {
                    unlink($filename);
                }
        }
        $result['msg']='jau:'.$_ARGS['op'];
        echo json_encode($result);

    }else if ( Administrador() && $_ARGS[2]=='write-cc-file'      ){
    
        $result = array();
        $result['error']=0;
        //include( SCRIPT_DIR_CLASSES.'/i18n.class.php');
        i18n::write_lang_file($_ARGS['lang_cc']);
        $result['msg']='Archivo guardado: '.$_ARGS['lang_cc'];
        echo json_encode($result);
  
    }else if ( Usuario()       && $_ARGS[2]=='newsletter'         ){ // from url: control_panel/ajax/newsletter/subscribe
        if( $_ARGS[3]=='subscribe'){
            $result = array();
            $result['error']=0;
            $result['msg'] = $_ARGS['email'].' subscrito al newsletter';
            echo json_encode($result);
        }
    }else if ( Root()          && $_ARGS[2]=='update'             ){

        #region update

        $zip_file =false;
        $host_extfw =  Str::end_with(  CFG::$vars['repo']['url'] ?? 'https://software.extralab.net' );
        
        if ($_ARGS[3]=='vendor'){
            include(SCRIPT_DIR_MODULE.'/functions.php');

            $phpversion = explode('.', PHP_VERSION);
            $zip_file = 'extfw_vendor.'. $phpversion[0].'.'.$phpversion[1];

            $result = download( $host_extfw.$zip_file.'.zip' , $zip_file.'.zip' , false);
            sleep(1);
            $ok = unzip("", $zip_file,false);
            sleep(2);

            //rmdirr('vendor');

        }else if ($_ARGS[3]=='module'||$_ARGS[3]=='theme'){
          
            $_type = $_ARGS[3]=='module'?'module':( $_ARGS[3]=='theme'?'theme':'plugin');

            if($_ARGS[3]=='module' || $_ARGS[3]=='theme'){
                $_ARGS[3]=$_ARGS[4];
            }

            include(SCRIPT_DIR_MODULE.'/functions.php');
            $zip_file = 'extfw_'.$_type .'_'.strtolower($_ARGS[3]);

            if (isset($_ARGS['host']) && strlen($_ARGS['host'])>5) $host_extfw = 'https://'.str_replace('https://','',Str::end_with($_ARGS['host'],'/'));

            //    echo  $host_extfw.$zip_file.'.zip'."n";
            //    print_r($_ARGS);

            $result = download( $host_extfw.$zip_file.'.zip' , $zip_file.'.zip' , false);
            sleep(1);
            $ok = unzip("", $zip_file,false);

            if(isset($_ARGS['redirect'])){
                $redirect = $_ARGS['redirect'];
            }else if($_type=='module'){
                $redirect = $_ARGS[3].'/install';
            }else{
                $redirect = ''; //'theme/'.$_ARGS[3];
            }

            $result['type'] = $_type;
            $result['name'] = $_ARGS[3];
            $result['redirect'] = $redirect;  //$_ARGS[3].'/install';
            sleep(2);
          
        }else if ($_ARGS[3]=='update'){
            include(SCRIPT_DIR_MODULE.'/functions.php');
            $zip_file = 'extfw_update';
            $result = download( $host_extfw.$zip_file.'.zip' , $zip_file.'.zip' , false,true);
            sleep(1);
            if($result['error']=='1'){
                $result['msg']='Ha ocurrido un error: '.print_r($result['msg'],true); 
            }else{

                if(file_exists(SCRIPT_DIR_JS.'/jquery'))            rmdirr(SCRIPT_DIR_JS.'/jquery');
                if(file_exists(SCRIPT_DIR_JS.'/jquery.modalform'))  rmdirr(SCRIPT_DIR_JS.'/jquery.modalform');
                if(file_exists(SCRIPT_DIR_JS.'/modernizr'))         rmdirr(SCRIPT_DIR_JS.'/modernizr');
                if(file_exists(SCRIPT_DIR_JS.'/without.jquery'))    rmdirr(SCRIPT_DIR_JS.'/without.jquery');
                if(file_exists(SCRIPT_DIR_JS.'/responsiveTabs'))    rmdirr(SCRIPT_DIR_JS.'/responsiveTabs');
                if(file_exists(SCRIPT_DIR_JS.'/swipebox'))          rmdirr(SCRIPT_DIR_JS.'/swipebox');
                if(file_exists(SCRIPT_DIR_JS.'/monaco'))            rmdirr(SCRIPT_DIR_JS.'/monaco');
                if(file_exists(SCRIPT_DIR_JS.'/cropper.js'))        rmdirr(SCRIPT_DIR_JS.'/cropper.js');
                if(file_exists(SCRIPT_DIR_JS.'/dropzone'))          rmdirr(SCRIPT_DIR_JS.'/dropzone');
                if(file_exists(SCRIPT_DIR_JS.'/prism'))             rmdirr(SCRIPT_DIR_JS.'/prism');
                if(file_exists(SCRIPT_DIR_JS.'/jquery'))            rmdirr(SCRIPT_DIR_JS.'/jquery');
                if(file_exists(SCRIPT_DIR_JS.'/pdf.js'))            rmdirr(SCRIPT_DIR_JS.'/pdf.js');

                sleep(2);

                $result = unzip("", $zip_file,false);
            }

            if ($result['error']==0){  
                $sql_sqls = array();  
                $sql_values = array();  
                if(!isset(CFG::$vars['users']['field']['country']))     $sql_values[] = "('users.field.country', 'false', '',  1)";
                if(!isset(CFG::$vars['users']['field']['county']))      $sql_values[] = "('users.field.county' , 'false', '',  1)";
                if(!isset(CFG::$vars['users']['field']['state']))       $sql_values[] = "('users.field.state'  , 'false', '',  1)";
                if(!isset(CFG::$vars['users']['field']['city']))        $sql_values[] = "('users.field.city'.  , 'false', '',  1)";
                if(!isset(CFG::$vars['modules']['contact']['bum']))     $sql_values[] = "('modules.contact.bum'    , 'true' , '',  1)"; 
                if(!isset(CFG::$vars['modules']['banners']['enabled'])) $sql_values[] = "('modules.banners.enabled', 'false', '',  1)"; 
                if(!isset(CFG::$vars['script']['version']))             $sql_values[] = "('script.version'         , '0.0.0', '',  1)"; 
                if(!isset(CFG::$vars['images']['max_image_w']))         $sql_values[] = "('images.max_image_w'     , '800'  , 'Reducir imágenes subidas a ésta anchura, en píxeles, si  son mas grandes',  1)";
                if(!isset(CFG::$vars['images']['max_image_h']))         $sql_values[] = "('images.max_image_h'     , '600'  , 'Reducir imágenes subidas a ésta altura, en píxeles, si  son mas grandes',  1)";
                if(!isset(CFG::$vars['images']['max_thumbnail_w']))     $sql_values[] = "('images.max_thumbnail_w' , '250'  , 'Anchura máxima de la miniaturas.',  1)";
                if(!isset(CFG::$vars['images']['keep_originals']))      $sql_values[] = "('images.keep_originals'  , 'true' , 'Si false, elimina las imágenes subidas cuando hayan sido reducidas. Se recomienda false si hay poco espacio.',  1)";
                if(!isset(CFG::$vars['images']['max_thumbnail_h']))     $sql_values[] = "('images.max_thumbnail_h' , '250'  , 'Altura máxima de las miniaturas',  1)";
                if(!isset(CFG::$vars['images']['webp']))                $sql_values[] = "('images.webp'            , 'false', 'Guardar versión webp de imágenes png y jpeg.\nRequiere PHP 7.2 o superior. ',  1)";
                if(!isset(CFG::$vars['images']['quality']))             $sql_values[] = "('images.quality'         , '90'   , '',  1)";
                if(!isset(CFG::$vars['module_security']))               $sql_values[] = "('module_security'        , 'false', '',  1)";
                if(!isset(CFG::$vars['captcha']['enabled']))            $sql_values[] = "('captcha.enabled'        , 'true' , '',  1)";
                if(!isset(CFG::$vars['login']['password']['strength'])) $sql_values[] = "('login.password.strength', '3'    , '',  1)";
                if(!isset(CFG::$vars['login']['default_method']))       $sql_values[] = "('login.default_method'   , 'password' , '',  1)";
                if(!isset(CFG::$vars['widget']['drawing']))             $sql_values[] = "('widget.drawing'         , 'false', '',  1)"; 
                if(!isset(CFG::$vars['widget']['news']))                $sql_values[] = "('widget.news'            , 'false', '',  1)"; 
                if(!isset(CFG::$vars['widget']['alerts']))              $sql_values[] = "('widget.alerts'          , 'false', '',  1)"; 
                if(!isset(CFG::$vars['widget']['links']))               $sql_values[] = "('widget.links'           , 'false', '',  1)"; 
                if(!isset(CFG::$vars['options']['submit_sitemap']))     $sql_values[] = "('options.submit_sitemap' , 'false', 'Si true envía sitemap.xml y robots.txt automáticamente (cuando se actualiza) a los buscadores',  1)"; 
                if(!isset(CFG::$vars['script']['updates']))             $sql_values[] = "('script.updates'         , 'true' , 'Habilita links para actualizaciones',  1)"; 
              //if(!isset(CFG::$vars['site']['langs']['shorcuts']))     $sql_values[] = "('site.langs.shortcuts'   , 'false', 'Permite diferentes urls para una misma sección.',  1)"; 
                if(!isset(CFG::$vars['site']['langs']['suffix']))       $sql_values[] = "('site.langs.suffix'      , 'false', 'Añadir  el código de idioma en las url\'s excepto si se trata del idioma por omisión.',  1)"; 
                if(!isset(CFG::$vars['repo']['url']))                   $sql_values[] = "('repo.url'               , 'https://software.extralab.net', '',  1)"; 
              //if(!isset(CFG::$vars['tinymce']['apikey']))             $sql_values[] = "('tinymce.apikey'         , ''     , 'Get in https://www.tiny.cloud/auth/signup/',  1)"; 

                if(!isset(CFG::$vars['options']['highlight_code']))     $sql_values[] = "('options.highlight_code', 'true', '', 1)";
                if(!isset(CFG::$vars['options']['highlight_engine']))   $sql_values[] = "('options.highlight_engine', 'prism', 'prism,highlightjs', 1)";

                if(!isset(CFG::$vars['login']['nostr']['enabled']))     $sql_values[] = "('login.nostr.enabled', 'false', '', 1)";
                if(!isset(CFG::$vars['login']['passwordless']['enabled']))  $sql_values[] = "('login.passwordless.enabled', 'true', '', 1)";
                if(!isset(CFG::$vars['plugins']['tip_ln']))             $sql_values[] = "('plugins.tip_ln', 'false', '', 1)";
                if(!isset(CFG::$vars['options']['use_cdn']))            $sql_values[] = "('options.use_cdn', 'true', 'Usa CDNs para librerías comunes (JQuery, Bootstrap, FontAwesome, etc)', '', 1)";
                if(!isset(CFG::$vars['options']['cdn_url']))            $sql_values[] = "('options.cdn_url', 'cdn.extralab.net', 'URL del CDN a utilizar', '', 1)";


                /*
                if(!isset(CFG::$vars['btcpayser']['url']))               $sql_values[] = "('btcpay.url'              , 'https://btcpay.queesbitcoin.net', '',  1)"; 
                if(!isset(CFG::$vars['btcpayser']['storeid']))           $sql_values[] = "('btcpay.storeid'          , '', '',  1)"; 
                if(!isset(CFG::$vars['btcpayser']['apikey']))            $sql_values[] = "('btcpay.apikey'           , '', '',  1)"; 
                if(!isset(CFG::$vars['btcpayser']['rpc_url']))           $sql_values[] = "('btcpay.rpc_url'          , 'https://btcpay.queesbitcoin.net/btcrpc/wallet/timestamping', '',  1)"; 
                if(!isset(CFG::$vars['btcpayser']['rpc_user']))          $sql_values[] = "('btcpay.rpc_user'         , '', '',  1)"; 
                if(!isset(CFG::$vars['btcpayser']['rpc_password']))      $sql_values[] = "('btcpay.rpc_password'     , '', '',  1)"; 
                if(!isset(CFG::$vars['btcpayser']['timestamp_cost']))    $sql_values[] = "('btcpay.timestamp_cost'   , '1000', '',  1)"; 
                if(!isset(CFG::$vars['btcpayser']['fallback_mempool']))  $sql_values[] = "('btcpay.fallback_mempool' , 'true', 'Use mempool instead Bitcoin Core RPC',  1)"; 

                noxtr.server_pubkey
                noxtr.server_privkey
                ai.grok.apikey
                ai.gemini.apikey
                ai.claude.apikey
                ai.deepseek.apikey
                ai.openai.apikey
                
                */                
                //

                //$sql = "INSERT INTO `CFG_CFG` SET `K` = ?, `V` = ?
                //                   ON DUPLICATE KEY UPDATE `V` = "?";
                //Db::get()->exec($query, [
                //    $request->K, $request->V,
                //    $request->V,
                //]);

                // ALTER TABLE ACL_USER_ROLES ADD COLUMN id INTEGER
                /*
                user_role_id INTEGER PRIMARY KEY AUTOINCREMENT,
                id_user INTEGER,
                id_role INTEGER,
                user_role_add_date datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (user_role_id), 
                UNIQUE KEY (id_user,id_role)
                */


                /***
                options.change_underscores
                server.ssh.password
                server.ssh.username
                server.ssh.port
                server.ssh.host
                widget.alerts		
                widget.links
                widget.drawing
                widget.news
                site.cookies.accept
                erp.enabled
                socket.apikey
                socket.server
                plugins.comments
                plugins.rating
                repo.dir
                repo.password
                repo.username
                repo.host
                repo.url
                */
                if(count($sql_values)>0){
                    // $sql = "INSERT INTO CFG_CFG (K,V,DESCRIPTION,ACTIVE) VALUES ".implode(',',$sql_values);
                    foreach ($sql_values as $sql){
                        $_sql = "INSERT INTO CFG_CFG (K,V,DESCRIPTION,ACTIVE) VALUES ".$sql;
                        Table::sqlExec($_sql);
                    }
                }               
                    
                ini_set('default_socket_timeout', 4);    
                $last_version = file_get_contents( trim( Str::end_with(  CFG::$vars['repo']['url'] ?? 'https://software.extralab.net/' ).'version/html'  ) );
                               
                $sql_sqls[]="UPDATE CFG_CFG SET V='".$last_version."' \nWHERE K='script.version'";
                
                if(count($sql_sqls)>0){
                    foreach($sql_sqls as $sql){
                        $res=Table::sqlExec($sql);
                    }
                }               

                t('RELOAD_CAPTCHA',' Cambiar captcha','',true);
                t('ZERO','cero','',true);
                t('ONE','uno','',true);
                t('TWO','dos','',true);
                t('THREE','tres','',true);
                t('FOUR','cuatro','',true);
                t('FIVE','cinco','',true);
                t('SIX','seis','',true);
                t('SEVEN','siete','',true);
                t('EIGHT','ocho','',true);
                t('NINE','nueve','',true);
                t('TEN','diez','',true);
                t('ELEVEN','once','',true);
                t('TWUELVE','doce','',true);
                t('THIRTEEN','trece','',true);
                t('FOURTEEN','catorce','',true);
                t('FIFTEEN','quince','',true);
                t('SIXTEEN','dieciseis','',true);
                t('TWENTY-FIVE','veinticinco','',true);
                t('THIRTY-SIX','treintayseis','',true);
                t('FORTY-NINE','cuarentaynueve','',true);
                t('SIXTY-FOUR','sesentaycuatro','',true);
                t('EIGHTY-ONE','ochentayuno','',true);
                t('HUNDRED','cien','',true);
                t('SPAM_PROTECTION','Protección anti-spam','',true);
                t('PLUS','mas','',true);
                t('MINUS','menos','',true);
                t('PER','por','',true);
                t('SQUARE_ROOT_OF','raiz cuadrada de','',true);
                t('SQUARED', 'al cuadrado','',true);
                t('NEWS', 'Noticias','News',true);
                t('DOCS', 'Documentos','Documents',true);
                t('YOUR_PHONE','Su teléfono','',true);
                t('COMMENTS','Comentarios','',true);
                t('SAVE','Guardar','',true);
                t('UNKNOWN','Desconocido','',true);
                t('MSG_TO_USER','Mensaje al usuario','',true);
                t('MSG_FROM_USER','Mensaje del usuario','',true);
              //t('ORDER_SENT','Pedido enviado','',true);
              //t('CONTACT_FORM_MSG','','',true);
                t('NEW_PASSWORD_SENT', 'Nueva contraseña enviada. Consulte su correo electrónico','', true);
                t('REMINDER_PASSWORD_REQUEST_SENT','Solicitud de recordatorio de contraseña enviada. Consulte su correo electrónico.','',true);
                t('PASSWORD_REMINDER_SUBJECT','Recordatorio de contraseña','Password reminder',true);
                t('PASSWORD_REMINDER_MSG','Use el siguiente enlace para recibir una nueva contraseña:','Use this link for receive a new password:',true);
                t('NEW_PASSWORD_MAIL_SUBJECT','Nueva contraseña','New password',true);
                t('RESET_PASSWORD_REQUEST_FAILED','No se ha podido restablecer la contraseña','',true);

                // rename media/NEWS/images TO media/NEWS/files
                // include (after_update.php)
                // include (after_update_<UPDATE_VERSION>.php)
                
                $result['msg']='Sistema actualizado!'; 
            }
            
            if(file_exists(SCRIPT_DIR_MODULES.'/install'))  rmdirr(SCRIPT_DIR_MODULES.'/install');
            
            sleep(2);
            
        }else if ($_ARGS[3]=='backup'){
       
            ini_set('memory_limit', '1024M');
            $result = array();
            $result['error']=0;
            $zip_file = 'pub_html-'.Str::sanitizeName($_SERVER['SERVER_NAME'],true).'-'.date('Ymd-his',time()).'.zip';
            include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');
            include(SCRIPT_DIR_MODULE.'/functions.php');
            rmdirr($zip_file);
            //echo 'Creating '.$zip_file;
            $hzip = createZipFile();
            addToZip($hzip,'.htaccess');
            addToZip($hzip,'README.md');
            addToZip($hzip,'robots.txt');
            if(file_exists('.user.ini'))addToZip($hzip,'.user.ini');
            if(file_exists('install.php'))addToZip($hzip,'install.php');
            addToZip($hzip,'index.php');
            addToZip($hzip,'configuration.php');
            addToZip($hzip,SCRIPT_DIR_MODULES);
            addToZip($hzip,SCRIPT_DIR_CLASSES);
            addToZip($hzip,SCRIPT_DIR_INCLUDES);
            addToZip($hzip,SCRIPT_DIR_LIB);
            addToZip($hzip,SCRIPT_DIR_IMAGES);
            addToZip($hzip,SCRIPT_DIR_JS);
            addToZip($hzip,SCRIPT_DIR_I18N);
            if(THEME!='default')
                addToZip($hzip,SCRIPT_DIR_THEMES.'/default');
            addToZip($hzip,SCRIPT_DIR_THEMES.'/'.THEME);
            addToZip($hzip,SCRIPT_DIR_OUTPUTS);
            addToZip($hzip,SCRIPT_DIR_PLUGINS);
            //addToZip($hzip,'vendor');
            //addToZip($hzip,'composer.json');
            if ( $_ARGS[2] == 'all' ) 
                addToZip($hzip,'media');
            saveZipFile($hzip,$zip_file);
            $result['url'] = $zip_file;
            $result['msg'] = $zip_file.' OK'; 


        }else if ($_ARGS[3]=='backmd'){

            ini_set('memory_limit', '1024M');
            $result = array();
            $result['error']=0;
            $zip_file = 'pub_html-'.Str::sanitizeName($_SERVER['SERVER_NAME'],true).'-MEDIA-'.date('Ymd-his',time()).'.zip';
            include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');
            include(SCRIPT_DIR_MODULE.'/functions.php');
            rmdirr($zip_file);
            //echo 'Creating '.$zip_file;
            $hzip = createZipFile();
            addToZip($hzip,'readme.txt');
            addToZip($hzip,SCRIPT_DIR_MEDIA.'/avatars');
            addToZip($hzip,SCRIPT_DIR_MEDIA.'/icons');
            addToZip($hzip,SCRIPT_DIR_MEDIA.'/images');
            addToZip($hzip,SCRIPT_DIR_MEDIA.'/user');
            addToZip($hzip,SCRIPT_DIR_MEDIA.'/page');
            saveZipFile($hzip,$zip_file);
            $result['url'] = $zip_file;
            $result['msg'] = $zip_file.' OK';

        }else if ($_ARGS[3]=='zip' && ($_ARGS[4]=='zip_module'||$_ARGS[4]=='zip_theme')){

            //FIX Check module and theme with same name
            $_SCRIPT_DIR_ = $_ARGS[4]=='zip_theme' ? SCRIPT_DIR_THEMES : SCRIPT_DIR_MODULES;
            $_ZIP_TYPE    = $_ARGS[4]=='zip_theme' ? 'theme' : 'module';

            if($_ARGS[4]=='zip_module' || $_ARGS[4]=='zip_theme'){
                //Vars::debug_var($_ARGS);
                $_ARGS[4]=$_ARGS[5];
            }

            $result = array();

            if(in_array($_ARGS[4],['control_panel',/*'edit','marketplace','pads','404','debug','_template_','default',*/'acl'])){

                $result['error']=1;
                $result['msg']='System module or theme:  '.$_ARGS[4];

            }else if(file_Exists($_SCRIPT_DIR_.'/'.$_ARGS[4])){
                $result['error']=0;
                include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');
                include(SCRIPT_DIR_MODULE.'/functions.php');
                $hzip = createZipFile();


                if(file_exists($_SCRIPT_DIR_.'/'.$_ARGS[4].'/zip.php')) 
                    include($_SCRIPT_DIR_.'/'.$_ARGS[4].'/zip.php');

                addToZip($hzip,$_SCRIPT_DIR_.'/'.$_ARGS[4]);

                $zip_file = 'extfw_'.$_ZIP_TYPE .'_'.$_ARGS[4].'.zip';
                rmdirr($zip_file);
                saveZipFile($hzip,$zip_file);

                sleep(2);
                if(file_exists($zip_file)){
                    if (CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password']){

                        $result = upload_file(CFG::$vars['repo']['host'],CFG::$vars['repo']['username'],CFG::$vars['repo']['password'],$zip_file,$zip_file,CFG::$vars['repo']['dir']?CFG::$vars['repo']['dir']:'/');
                        if($result['msg']=='ok') $result['msg'] = 'File '.$zip_file.' uploaded successfully to the remote repository '.CFG::$vars['repo']['host'];
                                            else $result['msg'] = 'Error uploading file '.$zip_file.' to the remote repository: '.print_r($result['msg'],true);
                    }
                    $result['url'] = $zip_file;
                    $result['url_version'] = str_replace('.zip','-'.CFG::$vars['script']['version'].'.zip',$zip_file);

                }else{
                    $result['error']=1;
                    $result['msg']='Cant create file '.$zip_file;
                }
            }else{
                
                $result['error']=1;
                $result['msg']='Module not exists '.$_SCRIPT_DIR_.'/'.$_ARGS[4];

            }

        }else if ($_ARGS[3]=='zip' && ($_ARGS[4]=='dbadmin')){

            $result = array();
            $result['error']=0;
            include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');
            include(SCRIPT_DIR_MODULE.'/functions.php');
            $hzip = createZipFile();
          //addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4]);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/init.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/install.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/index.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/ajax.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/run.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/runJS.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/style.css');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/'.$_ARGS[4].'/script.js');

            $zip_file = 'extfw_module_'.$_ARGS[4].'.zip';
            rmdirr($zip_file);
            saveZipFile($hzip,$zip_file);

            sleep(2);
            if(file_exists($zip_file)){
                if (CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password']){
                    $result = upload_file(CFG::$vars['repo']['host'],CFG::$vars['repo']['username'],CFG::$vars['repo']['password'],$zip_file,$zip_file,CFG::$vars['repo']['dir']?CFG::$vars['repo']['dir']:'/');
                }
                $result['url'] = $zip_file;
                 $result['url_version'] = str_replace('.zip','-'.CFG::$vars['script']['version'].'.zip',$zip_file);

            }

        }else if ($_ARGS[3]=='zip' && ($_ARGS[4]=='vendor')){
            
            ini_set('memory_limit', '1024M');
            $result = array();
            $result['error']=0;

            $phpversion = explode('.', PHP_VERSION);
            $zip_file = 'extfw_vendor.'. $phpversion[0].'.'.$phpversion[1].'.zip';
            //   $zip_file = 'extfw_vendor.zip';
            include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');
            include(SCRIPT_DIR_MODULE.'/functions.php');
            rmdirr($zip_file);
            //echo 'Creating '.$zip_file;
            $hzip = createZipFile();
            addToZip($hzip,'vendor');
            addToZip($hzip,'composer.json');
            saveZipFile($hzip,$zip_file);
            //$result['url'] = $zip_file;
            
            sleep(2);
            if(file_exists($zip_file)){
                if (CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password']){
                    $result = upload_file(CFG::$vars['repo']['host'],CFG::$vars['repo']['username'],CFG::$vars['repo']['password'],$zip_file,$zip_file,CFG::$vars['repo']['dir']?CFG::$vars['repo']['dir']:'/');
                }else{
                    $result['error']=0;
                    $result['msg']='Unable to upload the file to the remote repository: missing connection data.';
                }
                //$result['msg'] = $zip_file.' OK';     
                $result['url'] = $zip_file;
                $result['url_version'] = str_replace('.zip','-'.CFG::$vars['script']['version'].'.zip',$zip_file);

            }

        }else if ($_ARGS[3]=='zip'){   //  /control_panel/ajax/update/instal
                
            ini_set('memory_limit', '1024M');
            $result = array();
            $result['error']=0;
            include(SCRIPT_DIR_CLASSES.'/zip/zip.lib.php');
            include(SCRIPT_DIR_MODULE.'/functions.php');
            $compressed = false;
            $hzip = createZipFile();
            //if(file_exists('.user.ini'))addToZip($hzip,'.user.ini');
            //if(file_exists('install.php'))addToZip($hzip,'install.php');
            //addToZip($hzip,'.htaccess');
            addToZip($hzip,'index.php',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/sw.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_INCLUDES,false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/cookie.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/script.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/crypto-js.min.js'); //,false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/crypt.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/crypto.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/passwordless.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/sw.js',false,$compressed);

            //addToZip($hzip,SCRIPT_DIR_JS.'/default-passive-events.js');
          //addToZip($hzip,SCRIPT_DIR_JS.'/html2pdf.bundle.min.js');
            addToZip($hzip,SCRIPT_DIR_JS.'/wquery');
            addToZip($hzip,SCRIPT_DIR_JS.'/tooltip');
            addToZip($hzip,SCRIPT_DIR_JS.'/splitter');
          //addToZip($hzip,SCRIPT_DIR_JS.'/pdf.js');
          //addToZip($hzip,SCRIPT_DIR_JS.'/jquery');
            addToZip($hzip,SCRIPT_DIR_LIB.'/cropper.js');
            addToZip($hzip,SCRIPT_DIR_LIB.'/dropzone');
          //addToZip($hzip,SCRIPT_DIR_LIB.'/html5-qrcode/html5-qrcode.min.js');
            addToZip($hzip,SCRIPT_DIR_LIB.'/jsqr');
            
            addToZip($hzip,SCRIPT_DIR_LIB.'/qrcode/qrcode.min.js');
            addToZip($hzip,SCRIPT_DIR_LIB.'/jquery/jquery.qrcode.min.js');
            addToZip($hzip,SCRIPT_DIR_LIB.'/jquery/jquery.pwdMeter.js');
            addToZip($hzip,SCRIPT_DIR_LIB.'/jquery/jquery.effects.js');

            addToZip($hzip,SCRIPT_DIR_JS.'/pdfobject.min.js');
            addToZip($hzip,SCRIPT_DIR_JS.'/sortable.js');
          //addToZip($hzip,SCRIPT_DIR_JS.'/ace/ace.css');
          //  addToZip($hzip,SCRIPT_DIR_JS.'/monaco');
         // addToZip($hzip,SCRIPT_DIR_JS.'/modernizr');
         // addToZip($hzip,SCRIPT_DIR_JS.'/jquery.modalform',false,$compressed);
         // addToZip($hzip,SCRIPT_DIR_JS.'/ohSnap');
            addToZip($hzip,SCRIPT_DIR_JS.'/simpleTabs',false,$compressed);
          //addToZip($hzip,SCRIPT_DIR_JS.'/swipebox');
            addToZip($hzip,SCRIPT_DIR_JS.'/wysiwyg');
            addToZip($hzip,SCRIPT_DIR_JS.'/enhanced-select');
            
            addToZip($hzip,SCRIPT_DIR_JS.'/image_editor/image_editor.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_JS.'/image_editor/image_editor.css',false,$compressed);
            
            addToZip($hzip,SCRIPT_DIR_LIB.'/prism/prism.js',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_LIB.'/prism/prism.css',false,$compressed);

            addToZip($hzip,SCRIPT_DIR_OUTPUTS,false,$compressed);
            addToZip($hzip,SCRIPT_DIR_PLUGINS,false,$compressed);
            addToZip($hzip,SCRIPT_DIR_IMAGES);
            addToZip($hzip,SCRIPT_DIR_FONTS.'/CascadiaCode.ttf');
            addToZip($hzip,SCRIPT_DIR_FONTS.'/Monaco_5.1.ttf');
            addToZip($hzip,SCRIPT_DIR_CLASSES,false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/404');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/_template_');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/control_panel',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/debug',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/login',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/comments',false,$compressed);
          //addToZip($hzip,SCRIPT_DIR_MODULES.'/pads',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/acl',false,$compressed);
          //addToZip($hzip,SCRIPT_DIR_MODULES.'/docs',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/edit',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/marketplace',false,$compressed);
          //addToZip($hzip,SCRIPT_DIR_MODULES.'/dbadmin',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/mapa-web',false,$compressed);
          //addToZip($hzip,SCRIPT_DIR_MODULES.'/drawing',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_MODULES.'/install/init.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/install/index.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/install/create_db.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/install/create_tables_acl.php');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/install/create_tables_system.php');
          //addToZip($hzip,SCRIPT_DIR_MODULES.'/test');
            addToZip($hzip,SCRIPT_DIR_MODULES.'/page',false,$compressed);
            addToZip($hzip,SCRIPT_DIR_LIB.'/animate');
          //addToZip($hzip,SCRIPT_DIR_LIB.'/smartmenus');
            addToZip($hzip,SCRIPT_DIR_LIB.'/font-awesome');
            addToZip($hzip,SCRIPT_DIR_LIB.'/jxCart');
            addToZip($hzip,SCRIPT_DIR_LIB.'/phpids');
          //addToZip($hzip,SCRIPT_DIR_LIB.'/noble');
            addToZip($hzip,SCRIPT_DIR_LIB.'/phpmailer');
            addToZip($hzip,SCRIPT_DIR_LIB.'/redisent');
          //addToZip($hzip,SCRIPT_DIR_LIB.'/phpqrcode');        //deprecated
            addToZip($hzip,SCRIPT_DIR_LIB.'/ceca');
            addToZip($hzip,SCRIPT_DIR_LIB.'/redsys');
            addToZip($hzip,SCRIPT_DIR_LIB.'/sitemap-generator');
            addToZip($hzip,SCRIPT_DIR_LIB.'/simplehtmldom');
          //addToZip($hzip,SCRIPT_DIR_LIB.'/tinymce');            //FIX to marketplace/addons or 3party

          //addToZip($hzip,SCRIPT_DIR_LIB.'/monaco-editor');
            
          //addToZip($hzip,SCRIPT_DIR_LIB.'/suneditor');
            addToZip($hzip,SCRIPT_DIR_LIB.'/file_viewer');
            addToZip($hzip,SCRIPT_DIR_LIB.'/epub');
            addToZip($hzip,SCRIPT_DIR_LIB.'/chart.js');
            addToZip($hzip,SCRIPT_DIR_THEMES.'/example'); //,false,$compressed);
            addToZip($hzip,SCRIPT_DIR_THEMES.'/default'); //,false,$compressed);
            addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/audio/bum.mp3','media/audio/bum.mp3');
            if ($_ARGS[4]=='update'){    // /control_panel/ajax/update/instal/update
                $zip_file = 'extfw_update.zip';
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/robots.txt','robots.txt');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/README.md','README.md');
            }else{
                $zip_file = 'extfw_base.zip';
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/.htaccess','.htaccess');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/robots.txt','robots.txt');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/avatars/avatar.gif','media/avatars/avatar.gif');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/icons/icons.php','media/icons/icons.php');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/images/favicon.png','media/images/favicon.png');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/images/logo.png','media/images/logo.png');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/images/logo_email.png','media/images/logo_email.png');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/images/logo_footer.png','media/images/logo_footer.png');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/images/logo.webp','media/images/logo.webp');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/media/images/logo_footer.webp','media/images/logo_footer.webp');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/_i18n_/es.php','_i18n_/es.php');
                addFileToZip($hzip,SCRIPT_DIR_MODULES.'/install/base/_i18n_/en.php','_i18n_/en.php');
                addToZip($hzip,SCRIPT_DIR_MODULES.'/docs',false,$compressed);
                addToZip($hzip,SCRIPT_DIR_MODULES.'/contact',false,$compressed);
              //addToZip($hzip,SCRIPT_DIR_MODULES.'/news');
              //addToZip($hzip,SCRIPT_DIR_MODULES.'/docs');

            }
            rmdirr($zip_file);
            saveZipFile($hzip,$zip_file);

            $result['msg'] = $zip_file.' OK';    
            $result_installer = ['msg'=>'','error'=>0];
         
            if ($_ARGS[4]=='instal'){

                if(file_exists(SCRIPT_DIR_MODULES.'/install/base/install.php')){
                    $hzip_install = createZipFile();
                //addToZip($hzip_install,'install.php');
                //addToZip($hzip_install,'readme.txt');
                    addFileToZip($hzip_install,SCRIPT_DIR_MODULES.'/install/base/install.php','install.php');
                    addFileToZip($hzip_install,SCRIPT_DIR_MODULES.'/install/base/readme.txt','readme.txt');
                    saveZipFile($hzip_install,'extfw_installer.zip');
                    if (CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password'])
                        $result_installer = upload_file(CFG::$vars['repo']['host'],CFG::$vars['repo']['username'],CFG::$vars['repo']['password'],'extfw_installer.zip','extfw_installer.zip',CFG::$vars['repo']['dir']?CFG::$vars['repo']['dir']:'/');
                }

                if($result_installer['msg']=='ok') $result_installer['msg'] = 'File extfw_installer.zip uploaded successfully to the remote repository '.CFG::$vars['repo']['host'].'<br>';
                                            else $result_installer['msg'] = 'Error uploading file extfw_installer.zip to the remote repository: '.CFG::$vars['repo']['host'].'<br>';  
            }

            sleep(2);

            if(file_exists($zip_file)){
                if (CFG::$vars['repo']['host']&&CFG::$vars['repo']['username']&&CFG::$vars['repo']['password']){
                    $result = upload_file(CFG::$vars['repo']['host'],CFG::$vars['repo']['username'],CFG::$vars['repo']['password'],$zip_file,$zip_file,CFG::$vars['repo']['dir']?CFG::$vars['repo']['dir']:'/');
                    if($zip_file === 'extfw_base.zip'){
                        $nv = Str::increment_version(CFG::$vars['script']['version']);
                        Table::sqlExec("UPDATE CFG_CFG SET V='".$nv."' \nWHERE K='script.version'");
                        CFG::$vars['script']['version'] = $nv;
                    }
                }
            }

            if($result['msg']=='ok') $result['msg'] = $result_installer['msg'].'File '.$zip_file.' uploaded succesfully to the remote repository '.CFG::$vars['repo']['host'];
                                else $result['msg'] = $result_installer['msg'].'Error uploading file '.$zip_file.' to the remote repository: '.print_r($result['msg'],true);



           // $result['msg'] = 'hola';
            $result['url'] = $zip_file;
            $result['url_version'] = str_replace('.zip','-'.CFG::$vars['script']['version'].'.zip',$zip_file);
            
            sleep(2);

        }else if ($_ARGS[3]=='upload'){

          //upload_file('host','username','****','extfw_update.zip','extfw_update.zip'.'TEST','/domains/domain_name/public_html/software/');
          //$result = upload_file('host','username','******',$zip_file,$zip_file,'public_html/software/');

        }else if ($_ARGS[3]=='backdb'){

            //OK include(SCRIPT_DIR_MODULE.'/functions.php');
            //OK $result = backup_tables();
            //  /control_panel/ajax/update/backdb

            ini_set('memory_limit', '1024M');
            //include(SCRIPT_DIR_CLASSES.'/install.class.php');
            $result = Install::backup();    

        }else if ($_ARGS[3]=='restdb'){


            if (file_exists(SCRIPT_DIR.'/backup.php')){
              //include(SCRIPT_DIR.'/restore.sql')
              //foreach ($sqls as $sql){  Install::runsql($sql);    }
                //include(SCRIPT_DIR_CLASSES.'/install.class.php');
                $result = Install::restore(SCRIPT_DIR.'/backup.php');
            } else{
                $result = array();
                $result['error']=1;
                $result['msg'] = '<b style="font-weight:900;">No backup exists to restore.</b><br />You must upload a backup.php file or \'{DBNAME}.sqlite\' (If your database is Sqlite) to the public_html or equivalent.<br />
                                  The backup.php file can be created with the \'Backup database\' option in this same module.<br>
                                  <span style="color:yellow;font-size:11px;">In a future version we will add the option to upload the backup here.</span>';
            }

            //sleep(2);
        /********************************************************************************************************
        }else if ($_ARGS[3]=='instdb'){
                             
            include(SCRIPT_DIR_MODULE.'/functions.php');
            $result = create_tables_php_file();
            sleep(2);
        ********************************************************************************************************/ 

        }

        echo json_encode($result);   

    }else if ( Administrador() && $_ARGS[2]=='set-mode-auto'      ){
    
        $result = array();
        $result['error']=0;
        $_SESSION['translate']=!$_SESSION['translate'];
        $result['mode']=$_SESSION['translate']?t('YES'):t('NO');
        $result['msg']=t('TRANSLATION_MODE','Modo traducción').': '.($_SESSION['translate']?t('YES'):t('NO'));
        echo json_encode($result);

    }else if ( Administrador() && $_ARGS[2]=='write-sitemap-file' ){
    
        $result = array();
        $result['error']=0;
        include(SCRIPT_DIR_LIB.'/sitemap-generator/SitemapGenerator.php');
        define ("OUTPUT_FILE", "sitemap.xml");
        define ("SITE", $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']);
        //define ("FREQUENCY", "weekly");
        //define ("PRIORITY", "0.5");
        $sitemap = new SitemapGenerator(SITE.'/');
        $sitemap->robotsFileName = "robots.txt";
        /**/

        $_news = TableMysql::getFieldValue("SELECT item_public FROM CLI_ITEM WHERE item_name='news'" );
        //Vars::debug_var($_news,'_news');
        /**
        if(defined('MODULE_WINES') && MODULE_WINES!==false){
            $sql = "SELECT PRODUCT_ID AS ID,NAME,'1' AS PARENT,'/".MODULE_SHOP."/item' AS URL "      // MODULE_SHOP && MODULE_WINE
                 . "    FROM CLI_PRODUCTS WHERE ID_TIPO IN (SELECT ID FROM CLI_TIPOS WHERE ACTIVE=1) AND ID_BODEGA IN (SELECT ID FROM CLI_BODEGAS WHERE ACTIVE=1) AND ACTIVE=1 "
                 . " UNION SELECT item_id AS ID, '0' AS NAME, '0' AS PARENT,concat('/',item_url) AS URL FROM ".TB_ITEM." WHERE item_public='1' "
                 . " UNION SELECT ID,NAME,'1' AS PARENT,'/".MODULE_SHOP."/tipo'   AS URL FROM CLI_TIPOS          WHERE ACTIVE=1"
                 . " UNION SELECT ID,NAME,'2' AS PARENT,'/".MODULE_SHOP."/bodega' AS URL FROM CLI_BODEGAS        WHERE ACTIVE=1"   // MODULE_SHOP && MODULE_WINE
                 . " UNION SELECT ID,NAME,'3' AS PARENT,'/".MODULE_SHOP."/do'     AS URL FROM CLI_DENOMINACIONES WHERE ACTIVE=1"   // Get from Module file
                 . ($_news?" UNION SELECT NOT_ID AS ID, '0' AS NAME, '0' AS PARENT,concat('/news/',NOT_NAME) AS URL FROM NOT_NEWS WHERE ACTIVE='1'":'')   //FIX Get from Module file (check date, public state, etc)
                 ;
        }else */ if(MODULE_SHOP===true){
            $sql = "       SELECT PRODUCT_ID AS ID,           NAME,         '1' AS PARENT,'/".MODULE_SHOP."/item' AS URL FROM CLI_PRODUCTS WHERE ACTIVE=1 "
                 . " UNION SELECT item_id AS ID, '0' AS NAME, '0' AS PARENT,   concat('/',item_url) AS URL FROM ".TB_ITEM." WHERE item_public='1' "
                 . ($_news?" UNION SELECT NOT_ID AS ID, '0' AS NAME, '0' AS PARENT,concat('/news/',NOT_NAME) AS URL FROM NOT_NEWS WHERE ACTIVE='1'":'')   //FIX Get from Module file (check date, public state, etc)
                 ;
        }else{
            $sql = "SELECT item_id AS ID, '0' AS NAME, '0' AS PARENT,concat('/',item_url)  AS URL FROM ".TB_ITEM." WHERE item_public='1' "
                 .($_news?" UNION SELECT NOT_ID AS ID, '0' AS NAME, '0' AS PARENT,concat('/news/',NOT_NAME) AS URL FROM NOT_NEWS WHERE ACTIVE='1'":'')   //FIX Get from Module file (check date, public state, etc)
                 ;
        }

        $items = Table::sqlQuery($sql);


        //Vars::debug_var($items);
        $sitemap->addUrl(SITE,date('c'));

        foreach ($items as $item){
            if($item['URL']) $sitemap->addUrl(SITE.$item['URL'].($item['NAME']!='0'?'/'.Str::sanitizeName($item['NAME']):''),date('c'));
        }
        
        $sitemap->createSitemap();
        $sitemap->writeSitemap();
        $sitemap->updateRobots();
        if(CFG::$vars['options']['submit_sitemap']){
            $result = $sitemap->submitSitemap();
            $result['msg'] = 'El archivo sitemaps.xml ha sido actualizado y enviado a Google y a algún otro buscador.';
        }else{
            $result['msg'] = 'El archivo sitemaps.xml ha sido actualizado. No se ha enviado a ningún sitio.';
        }
        
        echo json_encode($result);

    }else if ( Administrador() && $_ARGS[2]=='save_file'          ){

        $result = array();
        $result['error']=0;

        // Verificar si se recibió un archivo
        if (empty($_FILES) || !isset($_FILES['file'])) {
            $result['error'] = 1;
            $result['msg'] = "No se recibió ningún archivo";
            echo json_encode($result);
            return;
        }

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 1;
            $result['msg'] = "Error en la subida del archivo. Código de error: " . $_FILES['file']['error'];
            echo json_encode($result);
            return;
        }

        //              $result['msg'] = "Imagen subida "; //.$_FILES['file']['tmp_name'];


        $arr_file_types = ['image/png'];
             
        $valid_img_ext = (in_array($_FILES['file']['type'], $arr_file_types)); 
        $ahora = time();
        $dir_media_module           = SCRIPT_DIR_MEDIA.'/images';
      //$filename = time() . '_' . Str::sanitizeName($_FILES['file']['name'],true);
        $filename = $_ARGS['type'].'.png';
        if (!file_exists(SCRIPT_DIR_MEDIA)) mkdir(SCRIPT_DIR_MEDIA, 0777);
        if (!file_exists($dir_media_module)) mkdir($dir_media_module, 0777);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $dir_media_module .'/'. $filename)){
            chmod($dir_media_module .'/'. $filename, 0644 ); //FIX
            if ($valid_img_ext)  miniatura($dir_media_module , $filename); 
            $result['hash']       = hash('crc32b',time());
            $result['dir_upload'] = $dir_media_module;
            $result['filename']   = $filename;
            $result['url']        = $dir_media_module.'/'.$filename;
            $result['thumb']      = $dir_media_module.'/'.TN_PREFIX.$filename;
            $result['msg']        = "Imagen subida ".$result['url']; //$_FILES['file']['tmp_name'];
            $sql = "UPDATE CFG_CFG SET V='".$result['hash']."' WHERE K='site.lastupdate'";
            Table::sqlExec($sql);
            png2webp( $dir_media_module.'/'.$filename );
        }else{
            $result['msg'] = "Error al subir la imagen ".$_FILES['file']['tmp_name'];
        }

        echo json_encode($result);



    }else if ( Root()          && $_ARGS[2]=='save_image'         ){

          
         // /control_panel/ajax/op=function/function=imagereceive/table=CLI_USER/id=4
        $result = array();
        $result['error']=0;
        $result['log'] ='';

        $_dirphotos = SCRIPT_DIR_MEDIA.'/images/';      //$this->colByName('user_url_avatar')->uploaddir;
        //$_type = $_ARGS['type'] ?? 'logo';
        $maxFileSize = 2 * 1024 * 1024; // 5 MB  
        $allowedExtensions = ['jpg', 'webp', 'jpeg', 'png', 'gif'];
        if (isset($_FILES['croppedImage']) && $_FILES['croppedImage']['error'] === UPLOAD_ERR_OK) {
            $result['src'] = explode('?',$_POST['src'])[0];
            $file = $_FILES['croppedImage'];

            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName      = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));

            $filePath = $_dirphotos . $fileName.'.'.$fileExtension;
            $result['src'] = $filePath ;
            
            if ($file['size'] > $maxFileSize) {
                $result['msg'] = 'El archivo es demasiado grande ('.$file['size'].' > '.$maxFileSize.'). El tamaño máximo es 5 MB.';
            } else if (!in_array($fileExtension, $allowedExtensions)) {
                $result['msg'] = 'Formato de archivo no permitido. Solo se permiten archivos JPG, PNG y GIF.';
            } else if (!file_exists($file['tmp_name'])) {
                $result['msg'] = 'No existe el archivo '.$file['tmp_name'];
            //}else if (!is_writable('/media/NEWS/files/11'))
            //    $result['msg'] = 'No se puede escribir en /media/NEWS/files/11';
            } else if (move_uploaded_file($file['tmp_name'], $filePath)) {
                
                $result['error'] = 0;
                $result['msg']   = 'La imagen se ha subido correctamente. Ruta: ' . $filePath;
                $result['image'] = $filePath;

                $result['msg'] = '<img src="/'.$filePath.'?hash='.time().'">';  //ID: '.$params['id'].'<br /> SQL: SELECT * FROM '.$prefix.'_user WHERE user_id='.$params['id'].'<br />
                $result['img']=$filePath.'?hash='.time();
                /* 

                $urlavatar = $this->getFieldValue('SELECT user_url_avatar FROM CLI_USER WHERE user_id = '.$params['id']);
                if(!$urlavatar){

                    //$_dirphotos = SCRIPT_DIR_MEDIA.'/avatars/';      //$this->colByName('user_url_avatar')->uploaddir;
                    //$_dirphotos_originals = SCRIPT_DIR_MEDIA.'/avatars/originals/';
                    //$foto = $_dirphotos . $params['id'].'.jpg';                    //( file_exists( $_dirphotos.'.tn_'.$_img) ? '.tn_'.$_img : $_img );
                    //$file = $_dirphotos . $params['id'].'.jpg'; //  'data.png';
                    //$foto_original = $_dirphotos_originals . $params['id'].'.jpg'; //( file_exists( $_dirphotos.'.t


                    $this->sql_query("UPDATE CLI_USER SET user_url_avatar='".$params['id'].'.jpg'."' WHERE user_id = ".$params['id']);
                }
                */

            } else {
                $result['msg']   = 'Hubo un error al subir la imagen. '.$filePath;
            }                      
            
        } else {
                $result['msg']   = 'No se recibió ninguna imagen o hubo un error en la subida.';
        }



        echo json_encode($result);











    }else if ( Root()          && $_ARGS[2]=='create_favicons'    ){

        $result = array();
        $result['error']=0;

        $php_ver = phpversion();
        if (strnatcmp($php_ver ,'7.2.0') >= 0){
            if (extension_loaded('gd')) {
                include(SCRIPT_DIR_MODULES.'/control_panel/create_favicons.php');
            } else {
                $result['msg'] = 'Se necesita la extensión GD de PHP para generar los iconos.';
            }
        }else{
            $result['msg'] = 'Su versión de php es la '.$php_ver.'. Se recomienda la versión 7.2 o superior para activar todas las características.';
        }
        echo json_encode($result);
        /**********
        require SCRIPT_DIR_CLASSES . '/ico.class.php' ;
        $result = array();
        $result['error']=0;

        $source = SCRIPT_DIR_MEDIA.'/images/favicon.png';
        $destination =  './favicon.ico'; //SCRIPT_DIR_MODULE.'/favicon.ico';
        $sizes = array(
            array( 16, 16 ),
            array( 24, 24 ),
            array( 32, 32 ),
            array( 48, 48 ),
        );

        $ico_lib = new PHP_ICO( $source, $sizes );
        $ico_lib->save_ico( $destination );

        //rename($destination , './favicon.ico');
        $applicationName = CFG::$vars['site']['title'];
        $faviconDir = 'media/icons/';
        $msapplicationTileColor = '#c07b39'; //'#FFF';
        $themeColor = '#c07b39'; //'#FFF';
        $titleBarColor = '#c07b39'; //'#FFF';
        $imageForAndroid = 'media/images/favicon.png';
        $imageForApple = 'media/images/favicon.png';
        $appleStartupImageProportion = 80.0;

        $faviconImageGenerator = new FaviconImageGenerator($applicationName, $faviconDir, $imageForAndroid, $imageForApple, $appleStartupImageProportion);
        $faviconImageGenerator->generate();
        $v = '?v='.date('dmYhms');

        $faviconHtmlGenerator = new FaviconHtmlGenerator($applicationName, $faviconDir, $msapplicationTileColor, $themeColor, $titleBarColor);

        $fichero = './media/icons/icons.php';
         
        $contenido = str_replace(' />'," />\n",$faviconHtmlGenerator->generate());
        if($hfp = fopen($fichero,'w')) fwrite($hfp,stripslashes($contenido));

        fclose($hfp);
        $result['msg'] = 'Favicons actualizados';
   
        echo json_encode($result);
        ********/   

    }else if ( Root()          && $_ARGS[2]=='form_cfg'           ){
        ?>
                <div class="info" style="margin-top:10px;margin-bottom:-5px;"><p>Estas pestañas sirven para configurar el archivo configuration.php que contiene la configuración inicial del sistema. Proceda con sumo cuidado.</p></div>
                <?php  
                    $form_cfg = new FORM('form_cfg','configuration.php');
                    $form_cfg->setAction( MODULE.'/ajax/op=save_cfg' );  //2.0
                    $default_theme_id = array_search(CFG::$vars['default_theme'], CFG::$vars['themes']);
                    $default_lang_id = array_search(CFG::$vars['default_lang'], CFG::$vars['langs']);
                    $fs_cfg = new fieldset('fs-cfg','Principal');
                    $fs_cfg->displaytype='tab';
                    $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2);
                    $fs_cfg->addElement(new formInput(   Field::field('prefix')         ->label('prefix')         ->placeholder('prefix')                                                                             , CFG::$vars['prefix']              ));
                    $fs_cfg->addElement(new formSelect(  Field::field('auth')           ->label('auth')           ->type('select')    ->values(['sqlite'=>'sqlite','mysql'=>'mysql','ldap'=>'ldap'])                  , CFG::$vars['auth']                ));
                    $fs_cfg->addElement(new formCheckbox(Field::field('enable_themes')  ->label('enable_themes')  ->type('bool')                                                        , CFG::$vars['enable_themes']       ));
                    $fs_cfg->addElement(new formInput(   Field::field('themes')         ->label('themes')         ->placeholder('themes')         ->len('500')                                                        , implode(',',CFG::$vars['themes']) ));
                    $fs_cfg->addElement(new formSelect(  Field::field('default_theme')  ->label('default_theme')  ->type('select')    ->values(CFG::$vars['themes'])->textafter(' ['.CFG::$vars['default_theme'].']') , $default_theme_id                 ));
                    $fs_cfg->addElement(new formCheckbox(Field::field('enable_langs')   ->label('enable_langs')   ->type('bool')                                                         , CFG::$vars['enable_langs']        ));
                    $fs_cfg->addElement(new formInput(   Field::field('langs')          ->label('langs')          ->placeholder('langs')->width('500')                                                                , implode(',',CFG::$vars['langs'])  ));
                    $fs_cfg->addElement(new formSelect(  Field::field('default_lang')   ->label('default_lang')   ->type('select')    ->values(CFG::$vars['langs'])->textafter(' '.$browser_lang)                     , $default_lang_id                  ));
                    $fs_cfg->addElement(new formCheckbox(Field::field('production')     ->label('production')     ->type('bool')                                                           , CFG::$vars['production']          ));
                    $fs_cfg->addElement(new formCheckbox(Field::field('log')            ->label('log')            ->type('bool')                                                           , CFG::$vars['log']                 ));
                    $fs_cfg->addElement(new formInput(   Field::field('default_module') ->label('default_module') ->placeholder('default_module')                                                                     , CFG::$vars['default_module']      ));
                    $fs_cfg->addElement(new formInput(   Field::field('default_page')   ->label('default_page')   ->placeholder('default_page')                                                                       , CFG::$vars['default_page']        ));
                    $fs_cfg->addElement(new formInput(   Field::field('module_security')->label('module_security')->placeholder('false or phpids or ??')                                                              , CFG::$vars['module_security']     ));

                    $fs_cfg->addElement(new formCheckbox(Field::field('log_attacks')  ->label('log_attacks')  ->type('bool')                                                                                         , CFG::$vars['log_attacks']       ));
                    $fs_cfg->addElement(new formInput(   Field::field('phpids_modules')->label('phpids_modules')->placeholder('false or modules')                                                                    , implode(',',CFG::$vars['phpids_modules']??['login','checkout', 'control_panel'])     ));

                    $fs_cfg->addElement(new formInput(   Field::field('module_shop')    ->label('module_shop')    ->placeholder('false or module name')                                                               , MODULE_SHOP                       ));
                    $fs_cfg->addElement(new formInput(   Field::field('wysiwyg_editor') ->label('wysiwyg_editor') ->placeholder('extfw, tinymce or none')                                                                   , defined('WYSIWYG_EDITOR')?WYSIWYG_EDITOR:''   ));

                    $fs_cfg->addElement( new formSelect(  Field::field('code_editor')   ->label('code_editor')   ->type('select')->values(['monaco'=>'Monaco Editor','ace'=>'ACE  Editor'])  , defined('CODE_EDITOR')?CODE_EDITOR:'monaco'   ) );


                    //if(defined('MODULE_SHOP')&&MODULE_SHOP){
                    //$fs_cfg->addElement(new formCheckbox(Field::field('module_wines')   ->label('module_wines')   ->type('bool')      ->placeholder('module_wines')                                                   , defined('MODULE_WINES')?MODULE_WINES:''       ));
                    //}
                    $fs_cfg->addElement(new formInput(   Field::field('master_password')->label('master_password')                    ->placeholder('master password')                                                , defined('MASTER_PASSWORD')?MASTER_PASSWORD:'' ));
                    //LOG $fs_cfg->addElement(new formCheckbox(Field::field('log')            ->label('log')                                ->type('bool')                                                                  , CFG::$vars['log']                ));

                    // in_array(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2),$cgf['langs']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'es';
                    $form_cfg->addElement($fs_cfg);

                    $fs_db = new fieldset('fs-db','Database');
                    $fs_db->displaytype='tab';
                    
                    $fs_db->addElement( new formSelect(  Field::field('dbtype')   ->label('dbtype')   ->type('select')->values(['sqlite'=>'sqlite','mysql'=>'mysql'])  , CFG::$vars['db']['type']  ) );
                    $fs_db->addElement( new formInput(   Field::field('dbhost')   ->label('dbhost')   ->placeholder('dbhost')                                          , CFG::$vars['db']['host']  ) );
                    $fs_db->addElement( new formInput(   Field::field('dbuser')   ->label('dbuser')   ->placeholder('dbuser')                                          , CFG::$vars['db']['user']  ) );
                    $fs_db->addElement( new formInput(   Field::field('dbpass')   ->label('dbpass')   ->placeholder('dbpass')                                          , CFG::$vars['db']['pass']  ));
                    $fs_db->addElement( new formInput(   Field::field('dbname')   ->label('dbname')   ->placeholder('dbname')                                          , CFG::$vars['db']['name']  ));
                    $fs_db->addElement( new formElementHtml(false,   '<hr>' ));
                    $fs_db->addElement( new formCheckbox(Field::field('dbcache')  ->label('dbcache')  ->type('bool')                                                   , CFG::$vars['db']['cache'] ));
                    $fs_db->addElement( new formCheckbox(Field::field('redis')    ->label('Redis')    ->type('bool')                                                   , CFG::$vars['redis'] ));
                    $fs_db->addElement( new formInput(   Field::field('cache_dir')->label('cache_dir')->parent('dbcache')->displaytype('inline')->placeholder('cache_dir')->len(100) ,  defined('CACHE_DIR')?CACHE_DIR:'' ) );
                    $fs_db->addElement( new formInput(   Field::field('redis_dsn')->label('redis_dsn')->parent('redis')  ->displaytype('inline')->placeholder('redis_dsn')->len(100) ,  CFG::$vars['redis_dsn']?CFG::$vars['redis_dsn']:'redis://localhost:6379' ) );
                    $fs_db->addElement( new formCheckbox(Field::field('dblog')    ->label('dblog')    ->type('bool')                                                   ,  CFG::$vars['db']['log']   ));
                    $form_cfg->addElement($fs_db);

                    $fs_ldap = new fieldset('fs-ldap','LDAP');
                    $fs_ldap->displaytype='tab';
                    $fs_ldap->addElement(new formInput(Field::field('ldap_server')->       label('ldap_server')->                 placeholder('ldap_server')                         , CFG::$vars['ldap_server']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_port')->         label('ldap_port')->                   placeholder('ldap_port')                           , CFG::$vars['ldap_port']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_context')->      label('ldap_context')->      len(150)->placeholder('ldap_context')                        , CFG::$vars['ldap_context']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_user')->         label('ldap_user')->                   placeholder('ldap_user')                           , CFG::$vars['ldap_user']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_password')->     label('ldap_password')->               placeholder('ldap_password')                       , CFG::$vars['ldap_password']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_bind_rdn')->     label('ldap_bind_rdn')->     len(150)->placeholder('ldap_bind_rdn')                       , CFG::$vars['ldap_bind_rdn']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_group_rdn')->    label('ldap_group_rdn')->    len(150)->placeholder('ldap_group_rdn')                      , CFG::$vars['ldap_group_rdn']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_group_admin')->  label('ldap_group_admin')->  len(150)->placeholder('members of this group will be admins'), CFG::$vars['ldap_group_admin']));
                    $fs_ldap->addElement(new formInput(Field::field('ldap_group_prefix')-> label('ldap_group_prefix')-> len(150)->placeholder('prefix for filter ldap groups')       , CFG::$vars['ldap_group_prefix']));
                    $form_cfg->addElement($fs_ldap);

                    $_boton = new Field();
                    $_boton->fieldname('btn')->javascript='submit_form_cfg();';
                    $form_cfg->addElement(new formButton($_boton,t('SAVE','Guardar')));

                    $form_cfg->render();   
                    
                ?>
                <pre id="debug-cfg">.....</pre>
                <script type="text/javascript">   

                    function submit_form_cfg(){
                        console.log('SUBMIT CFG');
                        var btn = $('#btn');
                        var datastring = $("#form_form_cfg").serialize();
                        $.ajax({
                               method: 'POST',
                               url: $('#form_form_cfg').attr('action'),
                               data:datastring, 
                               dataType: "json",
                               beforeSend: function( xhr, settings ) {
                                   $('.ajax-loader').show(); 
                                   btn.addClass('disabled');
                               }
                        }).done(function( data ) {
                            showMessageInfo( data.msg );
                            // $('#debug-cfg').append(data.msg);
                        }).fail(function(data) {
                            console.log(data);
                            showMessageError( data.msg );
                        }).always(function() {
                            $('.ajax-loader').hide();
                            btn.removeClass('disabled');
                        });                    }

                </script>
        <?php

    }else if ( Root()          && $_ARGS[2]=='menu_item_delete'   ){    

        $_item_id = $_ARGS['id'];
        $_option  = $_ARGS['op'];
        if (Vars::IsNumeric($_item_id)){


            $_item_url = TableMySql::getFieldValue( "SELECT item_url FROM ".TB_ITEM." WHERE item_id=".$_item_id);
            
            
            sleep(1);
            
            $_menu_items_to_delete = [];
            $_menu_items_to_delete[$_item_id] = ['item_id'=>$_item_id,'item_url'=>$_item_url];
            
            function get_item_menu_childs($id){
                
                global $_menu_items_to_delete;
                
                $_sql = "SELECT item_id,item_url FROM ".TB_ITEM." WHERE item_parent=".$id;
                $_rows = Table::sqlQuery($_sql);
                if($_rows){
                    foreach ($_rows as $_row){
                        $_menu_items_to_delete[$_row['item_id']] = $_row;
                        get_item_menu_childs($_row['item_id']);
                        //echo implode(' - ',$_row)."\n";
                    }
                }
                
            }


            //$_sql_t = "SELECT item_id FROM ".TB_PAGES." WHERE item_name='".$_item_url."'";
            //$_rows_t = Table::sqlQuery($_sql_t);

            if (file_exists(SCRIPT_DIR_MODULES.'/'.$_item_url)){

                ?><div class="alert"><p style="margin:20px auto;">Existe un módulo <?=$_item_url?>. No se puede eliminar.</p></div><?php

            }else{

                ?><div class="alert"><p style="margin:20px auto;">Va a eliminar un porrón de cosas, páginas, documentos, imágenes, etc.<br />Debajo de éste aviso puede ver un listado de lo que ocurrirá. Si está usted seguro dele al botón. Avisado queda.</p></div><?php

                get_item_menu_childs($_item_id);

                if($_option=='delete'){
                    echo '<pre style="max-width: 870px;font-size: 8px;">';
                }else{
                    echo '<style>#delete_recursively>ul{max-height:350px;overflow:auto;}#delete_recursively ul{padding-left:16px;}#delete_recursively li{font-size: 10px;padding:0}</style><ul>';
                    //echo  'DELETE FROM '.TB_ITEM.' WHERE item_id IN ('.implode(',',array_column($_menu_items_to_delete,'item_id')).')'."\n"; 
                    ?><li>Eliminará la entrada de menú '<?=$_item_url?>', sus submenus y sus páginas enlazadas. En total: <?=count($_menu_items_to_delete)?></li><?php
                }

                if($_option=='delete'){
                    Table::sqlExec( 'DELETE FROM '.TB_ITEM.' WHERE item_id IN ('.implode(',',array_column($_menu_items_to_delete,'item_id')).')' );
                }else{
                    //echo             "DELETE FROM ".TB_PAGES." WHERE item_name IN ('".implode('\',\'',array_column($_menu_items_to_delete,'item_url'))."')\n";
                    ?><li>Eliminará <span style="font-weight:700;"><?=count($_menu_items_to_delete)?></span> páginas: <?=  implode(', ',array_column($_menu_items_to_delete,'item_url'))?>.</li><?php
                }
                
                $_sql_id_pages_to_delete = "SELECT item_id FROM ".TB_PAGES." WHERE  item_name IN ('".implode('\',\'',array_column($_menu_items_to_delete,'item_url'))."')";           
                $_id_pages_to_delete = Table::sqlQuery($_sql_id_pages_to_delete);
                
                if ($_id_pages_to_delete){

                    if($_option=='delete'){

                    }else{
                        ?><li>Eliminará <span id="count_dirs">0</span> carpetas y <span id="count_files">0</span> documentos, con un tamaño de <span id="size_files">0</span>:<ul><?php
                    }
                    
                    $_count_dirs = 0;
                    $_count_files = 0;
                    $_files_to_delete_size = 0;
                    foreach ($_id_pages_to_delete as $_id){

                        $_dir_to_delete = SCRIPT_DIR_MEDIA.'/page/files/'.$_id['item_id'];
                        
                        if (file_exists($_dir_to_delete)) {
                            
                            //echo $_dir_to_delete.' OK'."\n";
                            ++$_count_dirs;
                            
                            foreach ( glob($_dir_to_delete.'/{,.}[!.,!..]*', GLOB_MARK|GLOB_BRACE) as $filename) {
                                
                                ++$_count_files;

                                if($_option=='delete'){
                                    if(unlink($filename)){
                                        echo 'unlink('.$filename.')'.' <span style="color:green;">OK</span>'."\n";
                                    }else{
                                        echo 'unlink('.$filename.')'.' <span style="color:red;">FAIL</span>'."\n";
                                    }
                                }else{
                                    $_size = filesize($filename);
                                    $_files_to_delete_size = $_files_to_delete_size  + $_size;
                                    ?><li><?=$filename?> <span style="color:silver;"><?=Str::formatBytesColorized($_size)?></span></li><?php
                                    //echo 'unlink('.$filename.')'.' <span style="color:green;">OK</span>'."\n";
                                }
                            }
                            
                            if($_option=='delete'){
                                if(rmdir($_dir_to_delete)){
                                    echo 'rmdir('.$_dir_to_delete.')'.' <span style="color:green;">OK</span>'."\n";
                                }else{
                                    echo 'rmdir('.$_dir_to_delete.')'.' <span style="color:red;">FAIL</span>'."\n";
                                }
                            }else{
                                ?><li><?=$_dir_to_delete?></li><?php
                                //echo 'unlink('.$_dir_to_delete.')'.' <span style="color:green;">OK</span>'."\n";
                            }
                        
                            
                        } else {
                            // echo 'El directorio '.$_dir_to_delete.' no existe'."\n"."\n";
                        }
                    }
                    ?>
                    <script>
                        setTimeout(function(){ $('#count_dirs') .text('<?=$_count_dirs?>')          .css('font-weight','700');  },1600);
                        setTimeout(function(){ $('#count_files').text('<?=$_count_files?>')         .css('font-weight','700');  },2100);
                        setTimeout(function(){ $('#size_files') .html('<?=Str::formatBytesColorized($_files_to_delete_size)?>').css('font-weight','700');  },2900);
                    </script>
                    <?php
                }
                    
                if($_option=='delete'){

                    Table::sqlExec( "DELETE FROM ".TB_PAGES." WHERE item_name IN ('".implode('\',\'',array_column($_menu_items_to_delete,'item_url'))."')" );
                    echo '</pre>';                       
                    echo '<script>$(\'#btnsubmit\').hide();$(\'#btnreset\').val(\''.t('CLOSE').'\');</script>';
                    
                }else{
                    
                    echo '</ul></li>';
                    echo '</ul>';
                    ?> 

                        <button id = "btn_delete_recursively" 
                            class = "btn_delete_recursively btn btn-danger"  style="width:100%;padding:20px;"
                            hx-get = "/control_panel/ajax/menu_item_delete/id=<?=$_item_id?>/op=delete"
                        hx-target = "#delete_recursively" 
                    hx-indicator = ".loader"
                        hx-trigger = "click"
                        hx-swap = "innerHTML"> Eliminar de verdad </button><div class="htmx-indicator loader"></div>
                    <?php
                }

            }
                    
            /*****
            $_sql_files_to_delete = "SELECT id_item,FILE_NAME FROM CLI_PAGES_FILES WHERE id_item IN (SELECT item_id FROM ".TB_PAGES." WHERE  item_name IN ('".implode('\',\'',array_column($_menu_items_to_delete,'item_name'))."'))";           
            $_files_to_delete = Table::sqlQuery($_sql_files_to_delete);
            echo $_sql_files_to_delete."\n";
            foreach ($_files_to_delete as $_file){
            echo $_file['id_item'].'/'.$_file['FILE_NAME'].' ';
            if (file_exists(SCRIPT_DIR_MEDIA.'/page/files/'.$_file['id_item'].'/'.$_file['FILE_NAME'])) echo 'OK'; else echo 'KO';
            echo  "\n";
            }
            *******/


        }else{

            echo 'Item id not valid: '.$_item_id;
        }


    }else if ( Root()          && $_ARGS[2]=='ssh'                ){ 
        
        $result = array();
        $result['error']=0;

       // $owner = get_current_user();
       // $group = posix_getgrgid(filegroup($_SERVER['DOCUMENT_ROOT'].'/index.php'))['name'];

        $cmd = $_ARGS['cmd'];

        //$cmd = 'cd '.$_SERVER['DOCUMENT_ROOT'].';pwd;cat fix.sh;find . -type f -exec chmod 644 {} \; find . -type d -exec chmod 755 {} \; find . -exec chown '.$owner.':'.$group.' {} \;ls -lha;';
        //OK     $cmd = 'cd '.$_SERVER['DOCUMENT_ROOT'].';pwd;cat fix.sh;find . -type f -exec chmod 644 {} \; find . -type d -exec chmod 755 {} \; find . -exec chown '.$owner.':'.$group.' {} \;ls -lha;';
        //$cmd = 'cd '.$_SERVER['DOCUMENT_ROOT'].';sh '.$_SERVER['DOCUMENT_ROOT'].'/fix.sh';
        //$cmd = 'cd '.$_SERVER['DOCUMENT_ROOT'].';ls -lha;sh '.$_SERVER['DOCUMENT_ROOT'].'/fix.sh';
        //$cmd = 'cd '.$_SERVER['DOCUMENT_ROOT'].';ls -lha;chown jts27n:www-data sales.1;ls -lha';
        //OKI  $cmd = 'cd '.$_SERVER['DOCUMENT_ROOT'].';ls -lha';
        //     $cmd = 'yum install redis-server';
        //     $cmd = 'yum install php-redis';

        if( //1==2 &&
            CFG::$vars['server']['ssh']['host'] &&
            CFG::$vars['server']['ssh']['port'] &&
            CFG::$vars['server']['ssh']['username'] &&
            CFG::$vars['server']['ssh']['password'] ){
            //include_once(SCRIPT_DIR_CLASSES . '/sshclient.class.php');

            define('SSH_VERBOSE',false);

            $ssh = new SSHClient();
            $ssh->verbose = SSH_VERBOSE;
            $ssh->host    = CFG::$vars['server']['ssh']['host']; // [SSH_HOST;
            $ssh->port    = intval(CFG::$vars['server']['ssh']['port']);
            $ssh->protocol   = 'ssh2';
            $ssh->username   = CFG::$vars['server']['ssh']['username'];
            $ssh->password   = CFG::$vars['server']['ssh']['password'];
            $ssh->remotedir = $_SERVER['DOCUMENT_ROOT'];
            $ssh->connect();
            
            //$ssh->exec('cd '.$_SERVER['DOCUMENT_ROOT']);

            $output = $ssh->exec($cmd);
            // $result['msg'] = $ssh->getLastError();
            // $result['msg'] = 'Errores:'.print_r($ssh->getErrors(),true);                     

            $result['command'] = $cmd;
            $result['msg'] = $output;

            if ($_ARGS[4]=='perms'){

                //$result['msg'] = $output;

            }
            //$result['error']=1;
            //$result['msg'] = 'Params:'.$_ARGS[4];
            //sleep(2);
            //echo json_encode($result);
        }else{

            $result['msg'] = $cmd;

        }

        echo json_encode($result);

    }else{

        if($_ARGS[2]=='update' || $_ARGS[2]=='op=save_cfg' || $_ARGS[2]=='op=save_file' || $_ARGS[2]=='op=create_favicons'){
            $result = array();
            $result['error']=1;
            $result['msg'] = 'Acceso denegado.';
            echo json_encode($result);
            //return;
        } else{
            include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');
        }
        
    }


