<?php


$classmap = [
    'APP'            => SCRIPT_DIR_CLASSES . '/app.class.php',
    'CFG'            => SCRIPT_DIR_CLASSES . '/config.class.php',
    //'Router'         => SCRIPT_DIR_CLASSES . '/router.class.php',
    //'AuthLDAP'       => SCRIPT_DIR_CLASSES . '/ldap.class.php',  //ldap.class.php',
    'AuthLDAP'       => SCRIPT_DIR_CLASSES . '/auth.ldap.class.php',  //ldap.class.php',
    'AuthDEMO'       => SCRIPT_DIR_CLASSES . '/auth.ldap.class.php',  
    //'LDAPutils'   => SCRIPT_DIR_CLASSES . '/ldap.utils.class.php',
    'ACL'            => SCRIPT_DIR_CLASSES . '/acl.class.php',            
    'LOG'            => SCRIPT_DIR_CLASSES . '/log.class.php',            
    'Location'       => SCRIPT_DIR_CLASSES . '/location.class.php',
    'Login'          => SCRIPT_DIR_CLASSES . '/login.class.php',
    'Banner'         => SCRIPT_DIR_CLASSES . '/banner.class.php',             
    'Mailer'         => SCRIPT_DIR_CLASSES . '/mail.class.php',
    'BARCODE'        => SCRIPT_DIR_CLASSES . '/barcode.class.php',            
    'Breadcrumb'     => SCRIPT_DIR_CLASSES . '/breadcrumb.class.php',         
    'Browser'        => SCRIPT_DIR_CLASSES . '/browser.class.php',            
    'MarkdownParser' => SCRIPT_DIR_CLASSES . '/markdown.class.php',
    'MyCache'        => SCRIPT_DIR_CLASSES . '/cache.class.php',              
    'Captcha'        => SCRIPT_DIR_CLASSES . '/captcha.class.php',            
    'MemVar'         => SCRIPT_DIR_CLASSES . '/memvar.class.php',
    'Comments'       => SCRIPT_DIR_CLASSES . '/comments.class.php',
    'Karma'          => SCRIPT_DIR_CLASSES . '/karma.class.php',
    'Invitation'     => SCRIPT_DIR_CLASSES . '/invitation.class.php',
    //'' => SCRIPT_DIR_CLASSES . '/backup.class.php',             
    //'' => SCRIPT_DIR_CLASSES . '/dates.class.php',              
    //'' => SCRIPT_DIR_CLASSES . '/singletonize.class.php',
    //'' => SCRIPT_DIR_CLASSES . '/images.class.php',             
    //'' => SCRIPT_DIR_CLASSES . '/captcha.recaptcha.class.php',  
    'Menu'           => SCRIPT_DIR_CLASSES . '/menu.class.php',
    'Messages'       => SCRIPT_DIR_CLASSES . '/messages.class.php',
    'Rating'         => SCRIPT_DIR_CLASSES . '/rating.class.php',
    'PHPWebSocket'   => SCRIPT_DIR_CLASSES . '/class.WebSocket.php',
    'Crypt'          => SCRIPT_DIR_CLASSES . '/crypt.class.php',
    'CryptoLib'      => SCRIPT_DIR_CLASSES . '/crypto.class.php',
    'PasswordlessAuth'=> SCRIPT_DIR_CLASSES . '/passwordless.class.php',
    'NostrAuth'      => SCRIPT_DIR_CLASSES . '/nostrauth.class.php',              
    'WebSocketClient'=> SCRIPT_DIR_CLASSES . '/websocketclient.class.php',
    'NostrRelayClient'=> SCRIPT_DIR_CLASSES . '/nostrrelayclient.class.php',
    'NoxtrStore'     => SCRIPT_DIR_MODULES . '/noxtr/noxtrstore.class.php', 
    'NostrCrypto'    => SCRIPT_DIR_MODULES . '/noxtr/nostrcrypto.class.php',
    'Str'            => SCRIPT_DIR_CLASSES . '/str.class.php',
    'Vars'           => SCRIPT_DIR_CLASSES . '/vars.class.php',
    'SYS'            => SCRIPT_DIR_CLASSES . '/sys.class.php',
    'Tabs'           => SCRIPT_DIR_CLASSES . '/tabs.class.php',
    'Inflect'        => SCRIPT_DIR_CLASSES . '/inflect.class.php',         
    'XSS'            => SCRIPT_DIR_CLASSES . '/xss.class.php',
    'XssHtml'        => SCRIPT_DIR_CLASSES . '/xss.class.php',
    'SecurityValidator' => SCRIPT_DIR_CLASSES . '/security.validator.class.php',
    'Redis'          => SCRIPT_DIR_CLASSES . '/redis.class.php',
    'Plugins'        => SCRIPT_DIR_CLASSES . '/plugins.class.php',
    'Shortcodes'     => SCRIPT_DIR_CLASSES . '/shortcodes.class.php',
    'Hook'           => SCRIPT_DIR_CLASSES . '/hooks.class.php',              
    
    
    'paginator'        => SCRIPT_DIR_CLASSES . '/paginator.class.php',
    'MySql_PDO'        => SCRIPT_DIR_CLASSES . '/db/db.mysql.class.php',
    'SQLite_PDO'       => SCRIPT_DIR_CLASSES . '/db/db.sqlite.class.php',  
    'OracleConnection' => SCRIPT_DIR_CLASSES . '/db/db.oracle.class.php',  
    'MysqlConnection'  => SCRIPT_DIR_CLASSES . '/db/connection.mysql.trait.php',    
    'SQLiteConnection' => SCRIPT_DIR_CLASSES . '/db/connection.sqlite.trait.php',  
    'OracleConnection' => SCRIPT_DIR_CLASSES . '/db/connection.oracle.trait.php',  
    'DemoConnection'   => SCRIPT_DIR_CLASSES . '/db/connection.demo.trait.php',  

    'JS'               => SCRIPT_DIR_CLASSES . '/scaffold/table.class.php',
    'Table'            => SCRIPT_DIR_CLASSES . '/scaffold/table.class.php',
    'TableMysql'       => SCRIPT_DIR_CLASSES . '/scaffold/table.mysql.class.php',
    'TableSqlite'      => SCRIPT_DIR_CLASSES . '/scaffold/table.sqlite.class.php',
    'TableOracle'      => SCRIPT_DIR_CLASSES . '/scaffold/table.oracle.class.php',
    'Field'            => SCRIPT_DIR_CLASSES . '/scaffold/field.class.php',

    'dummyField'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'fieldset'          => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'FORM'              => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formElement'       => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formElementHtml'   => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formInput'         => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formTextarea'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formInputProgress' => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    //'formInputCCC'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formSubmit'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formButton'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formHidden'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    //'NEW_formInputColor'=> SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formInputColor'    => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formInputDate'     => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formInputDateTime' => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formInputTime'     => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formSelect'        => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'BaseFormSelectDb'  => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formSelectDb'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formCheckbox'      => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formRadio'         => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formFile2'         => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
    'formFile'          => SCRIPT_DIR_CLASSES . '/scaffold/form.class.php',
        
    'iEvents'                => SCRIPT_DIR_CLASSES . '/scaffold/table.events.class.php',
    'defaultTableEvents'     => SCRIPT_DIR_CLASSES . '/scaffold/table.events.class.php',
    'defaultTableEventsTags' => SCRIPT_DIR_CLASSES . '/scaffold/table.events.tags.class.php',
    
    'Install'           => SCRIPT_DIR_CLASSES . '/install.class.php',
    'WebServiceMRW'     => SCRIPT_DIR_CLASSES . '/mrw.class.php',
    'NgramComparator'   => SCRIPT_DIR_CLASSES . '/ngram.class.php',
    'PDF'               => SCRIPT_DIR_CLASSES . '/pdf.class.php',
    'Persistent'        => SCRIPT_DIR_CLASSES . '/persistent.class.php',
    'delta'             => SCRIPT_DIR_CLASSES . '/delta.class.php',             
    'AuthDEMO'          => SCRIPT_DIR_CLASSES . '/demo.class.php',               
    'RateLimiter'       => SCRIPT_DIR_CLASSES . '/ratelimiter.class.php',
    'Encoding'          => SCRIPT_DIR_CLASSES . '/encoding.class.php',    
    'FixUtf8'           => SCRIPT_DIR_CLASSES . '/encoding.class.php',               
    'Errors'            => SCRIPT_DIR_CLASSES . '/errors.class.php',             
    'HtmlToText'        => SCRIPT_DIR_CLASSES . '/html2text.class.php',          
    'Sitemap'           => SCRIPT_DIR_CLASSES . '/sitemap.class.php',
    'HTML'              => SCRIPT_DIR_CLASSES . '/html.class.php',               
    'SSHClient'         => SCRIPT_DIR_CLASSES . '/sshclient.class.php',
    'i18n'              => SCRIPT_DIR_CLASSES . '/i18n.class.php',               
    'PHP_ICO'           => SCRIPT_DIR_CLASSES . '/ico.class.php',        

    'zipfile'           => SCRIPT_DIR_CLASSES . '/zip.class.php',        
    'Template'          => SCRIPT_DIR_CLASSES . '/template.class.php',        

    'MySQLStorage'      => SCRIPT_DIR_CLASSES . '/storage/mysql.storage.class.php',        
    'SQLiteStorage'     => SCRIPT_DIR_CLASSES . '/storage/sqlite.storage.class.php',        
    'RedisStorage'      => SCRIPT_DIR_CLASSES . '/storage/redis.storage.class.php',        
    'SessionStorage'    => SCRIPT_DIR_CLASSES . '/storage/session.storage.class.php',        
    'StorageInterface'  => SCRIPT_DIR_CLASSES . '/storage/storage.interface.php',        

    'EDIT_ware'         => SCRIPT_DIR_MODULES . '/edit/edit_ware.class.php'


    /*
    '' => SCRIPT_DIR_CLASSES . '/.class.php',        
    '' => SCRIPT_DIR_CLASSES . '/.class.php',        
    '' => SCRIPT_DIR_CLASSES . '/.class.php',        

    checkout                     
    db                           
    crud                         
    exceptions                   
    scaffold
    storage
    imap                         
    zip
    */
    
];

spl_autoload_register(function ($class) use ($classmap) {
    if (isset($classmap[$class])) {
        require_once $classmap[$class];
    }
});
