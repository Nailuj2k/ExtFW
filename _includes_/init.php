<?php

        if (PHP_SAPI === 'cli') {
            chdir(dirname(__DIR__));

            $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);

            $cli_host = 'localhost';
            $root_dir_name = basename(dirname($_SERVER['DOCUMENT_ROOT']));
            if (preg_match('/^(?:[a-z0-9-]+\.)+[a-z]{2,}$/i', $root_dir_name)) {
                $cli_host = strtolower($root_dir_name);
            }

            if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === '' || $_SERVER['HTTP_HOST'] === 'localhost') {
                $_SERVER['HTTP_HOST'] = $cli_host;
            }
            $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'];

            //ejemplo: php index.php noxtr/server/action=monitor/dry_run=1
 
            $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/' . ltrim((string) ($_SERVER['argv'][1] ?? 'noxtr/server'), '/');
            $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en';
            $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $_SERVER['SCRIPT_NAME'] = '/index.php';
            $_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? '';
        }

        $cfg['outputs']          = array('ajax','html','pdf','csv','server','json','raw','file','api','txt'/*,'print','xml','doc','xls','none','rss'*/);
        $cfg['accepted_doc_extensions'] = array( '.doc','.docx','.xls','.xlsx','.ppt','.pptx','.mdb','.dot','.dotx',
                                            '.pages','.numbers','.keynote',
                                            '.epub','.mobi',
                                            '.odt', '.ods', '.odp',
                                            '.pdf','.rtf','.txt','.dwg',
                                            '.html','.xml',
                                            '.zip','.rar','.7z','.tar','.tgz','.gz',
                                            '.ico','.tif','.tiff','.psd','.bmp','.eps','.ai',
                                            '.jpg','.jpeg','.gif','.png','.webp',
                                            '.avi','.wmv','.mpg','.mpeg','.mp3','.ogg','.mp4','.m4a','.mov','.mkv') ;
        $cfg['accepted_img_extensions'] = array( '.jpg','.jpeg','.gif','.png','.webp') ;
        define('TB_CFG'             , 'CFG_CFG');
        define('TB_TPL'             , 'CFG_TPL');
        define('TB_SLIDER'          , 'CFG_SLIDER');
        define('TB_LANG'            , 'CFG_LANG');
        define('TB_STR'             , 'CFG_STR');
        define('TB_CC'              , 'CFG_CC');
        define('TB_PAIS'            , 'CFG_PAIS');
        define('TB_PROVNCIA'        , 'CFG_PROVINCIA');
        define('TB_MUNICIPIO'       , 'CFG_MUNICIPIO');
        define('TB_LOCALIDAD'       , 'CFG_LOCALIDAD');
        define('TB_EXTRA_FIELDS'    , 'CFG_EXTRA_FIELDS');
        define('TB_USER'            , 'CLI_USER');
        define('TB_ITEM'            , 'CLI_ITEM');
        define('TB_PAGES'           , 'CLI_PAGES');
        define('TB_LOG'             , 'LOG_EVENTS');
        define('TB_TAGS'            , 'CLI_TAGS');
        define('TB_ACL_ROLES'       , 'ACL_ROLES');
        define('TB_ACL_PERMISSIONS' , 'ACL_PERMISSIONS');
        define('TB_ACL_ROLE_PERMS'  , 'ACL_ROLE_PERMS');
        define('TB_ACL_USER_PERMS'  , 'ACL_USER_PERMS');
        define('TB_ACL_USER_ROLES'  , 'ACL_USER_ROLES');
        define('TB_ACL_ITEM_ROLES'  , 'ACL_ITEM_ROLES');
        define('TN_PREFIX'          , '.tn_' );    // prefijo para miniaturas
        define('BIG_PREFIX'         , '.big_' );   // prefijo para originales
        define('SCRIPT_DIR_MODULES' , '_modules_'  ); 
        define('SCRIPT_DIR_CLASSES' , '_classes_'  ); 
        define('SCRIPT_DIR_INCLUDES', '_includes_' ); 
        define('SCRIPT_DIR_LIB'     , '_lib_'      ); 
        define('SCRIPT_DIR_IMAGES'  , '_images_'   ); 
        define('SCRIPT_DIR_JS'      , '_js_'       ); 
        define('SCRIPT_DIR_I18N'    , '_i18n_'     ); 
        define('SCRIPT_DIR_THEMES'  , '_themes_'   ); 
        define('SCRIPT_DIR_OUTPUTS' , '_outputs_'  ); 
        define('SCRIPT_DIR_PLUGINS' , '_plugins_'  );
        define('SCRIPT_DIR_FONTS'   , '_fonts_'    ); 



        if ( ! function_exists( 'str_starts_with' ) ) {
            function str_starts_with ( $haystack, $needle ) {
            return strpos( $haystack , $needle ) === 0;
            }
        }

        if ( ! function_exists( 'str_ends_with' ) ) {
            function str_ends_with( $haystack, $needle ) {
                if ( '' === $haystack && '' !== $needle ) return false;
                $len = strlen( $needle );
                return 0 === substr_compare( $haystack, $needle, -$len, $len );
            }
        }

        // based on original work from the PHP Laravel framework
        if (!function_exists('str_contains')) {
            function str_contains($haystack, $needle) {
                return $needle !== '' && mb_strpos($haystack, $needle) !== false;
            }
        }

        
        $cfg['ssl']              = !str_ends_with($_SERVER['HTTP_HOST'] ?? '', '.onion');
        $cfg['proto']            = $cfg['ssl'] ? 'https://' : 'http://';

        define('SCRIPT_HOST'        , $cfg['proto'].$_SERVER['HTTP_HOST'] ); 
        define('SCRIPT_DIR_MEDIA'   , 'media'      ); 
        define('SCRIPT_DIR_LOG'     , SCRIPT_DIR_MEDIA.'/log'  );
        define('ROOT_DIR'           , dirname($_SERVER['DOCUMENT_ROOT']) ); 
        define('SCRIPT_DIR'         , str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ) );        
