<?php 

define('DOT','-');

class MyCache{

    private static $instance = null;
    public static $cachetime = 10000;  // segundos
    public static $prefix = 'cache'.DOT;
    public static $ext = '.txt';
    private static $sep = DOT;
    public static $memvar = false;
    public static $redis = false;
    public static $redis_server;
    public static $tbn = array();  // table to numbers
    public static $mode = 3;  // Table idenfifiers as: 1 Numbers, 2 CRC's, 3 Names

    function __construct(){

        self::$redis  = CFG::$vars['redis'];
        self::$memvar = CFG::$vars['memvar'];

         if (!is_dir(CACHE_DIR) && !@mkdir(CACHE_DIR, 0777, true)) {
            Messages::error('The directory '.CACHE_DIR.' is invalid.',false,30000);
        }

        if (!is_writable(CACHE_DIR)) {
            Messages::error('Cache cachedir is not writable: "' . CACHE_DIR . '".',false,30000);
        }

        if(self::$redis){

            require_once SCRIPT_DIR_CLASSES.'/redis.class.php';
            //self::$redis_server = new redisent\Redis('redis://localhost');
            self::$redis_server = new Redis(CFG::$vars['redis_dsn']);  //'redis://localhost');
            if(self::$redis_server->connected){
                \Messages::success('Redis server connected OK.');
            }else{
                \Messages::error('No se puede conectar con el servidr Redis.'); // ['.CFG::$vars['redis_dsn'].']');
            }

        }else{

        }
       
        Messages::info('Cache initialized',false,2000);

        $n = 10; 
        if(self::$mode==1){    // Table idenfifiers as: 1 Numbers, 2 CRC's, 3 Names
            self::$tbn[$n++] = TB_CFG;              // CFG_CFG
            self::$tbn[$n++] = TB_TPL;              // CFG_TPL
            self::$tbn[$n++] = TB_STR;              // CFG_STR
            self::$tbn[$n++] = TB_CC;               // CFG_CC
            self::$tbn[$n++] = TB_LANG;             // CFG_LANG
            self::$tbn[$n++] = TB_USER;             // CLI_USER
            self::$tbn[$n++] = TB_USER.'_ADDRESSES';   
            self::$tbn[$n++] = TB_ITEM;             // CLI_ITEM
            self::$tbn[$n++] = TB_PAGES;            // CLI_PAGES
            self::$tbn[$n++] = TB_PAGES.'_FILES';
            self::$tbn[$n++] = TB_PAGES.'_FILES_TAGS';
            self::$tbn[$n++] = TB_TAGS;             // CLI_TAGS
            self::$tbn[$n++] = TB_ACL_ROLES;        // ACL_ROLES
            self::$tbn[$n++] = TB_ACL_PERMISSIONS;  // ACL_PERMISSIONS
            self::$tbn[$n++] = TB_ACL_ROLE_PERMS;   // ACL_ROLE_PERMS
            self::$tbn[$n++] = TB_ACL_USER_PERMS;   // ACL_USER_PERMS
            self::$tbn[$n++] = TB_ACL_USER_ROLES;   // ACL_USER_ROLES
            self::$tbn[$n++] = TB_ACL_ITEM_ROLES;   // ACL_ITEM_ROLES
            self::$tbn[$n++] = TB_LOG;              // LOG_EVENTS
            self::$tbn[$n++] = TB_PAIS;             // CFG_PAIS
            self::$tbn[$n++] = TB_PROVNCIA;         // CFG_PROVINCIA
            self::$tbn[$n++] = TB_MUNICIPIO;        // CFG_MUNICIPIO
            self::$tbn[$n++] = TB_LOCALIDAD;        // CFG_LOCALIDAD
            self::$tbn[$n++] = TB_EXTRA_FIELDS;     // CFG_EXTRA_FIELDS
            self::$tbn[$n++] = TB_SLIDER;           // CFG_SLIDER
            self::$tbn[$n++] = 'CFG_CLICKS';

            self::$tbn[$n++] = 'CLI_CATEGORIES';
            self::$tbn[$n++] = 'CLI_DESTINOS';
            self::$tbn[$n++] = 'CLI_TARIFAS';
            self::$tbn[$n++] = 'CLI_AGENCIAS';
            self::$tbn[$n++] = 'CLI_TAX';
            self::$tbn[$n++] = 'CLI_PRODUCTS';
            self::$tbn[$n++] = 'CLI_PRODUCT_IMAGES';
            self::$tbn[$n++] = 'CLI_COUPONS';
            self::$tbn[$n++] = 'CLI_ORDERS';
            self::$tbn[$n++] = 'CLI_ORDER_LINES';

            self::$tbn[$n++] = 'GES_BANNERS';
            self::$tbn[$n++] = 'GES_BANNERS_TYPES';
            self::$tbn[$n++] = 'GES_BANNERS_LOG';

            self::$tbn[$n++] = 'CFG_FILES_PROVIDER';
            self::$tbn[$n++] = 'CFG_CALENDAR';
            self::$tbn[$n++] = 'CFG_LINKS';
            self::$tbn[$n++] = 'CFG_ALERTS';
            self::$tbn[$n++] = 'CFG_ALERTS_FILES';

            self::$tbn[$n++] = 'NOT_NEWS';
            self::$tbn[$n++] = 'NOT_NEWS_FILES';
            self::$tbn[$n++] = 'NOT_NEWS_TAGS';
            self::$tbn[$n++] = 'NOT_TAGS';

            self::$tbn[$n++] = 'CFG_APPS';
            self::$tbn[$n++] = 'CFG_APPS_PERMS';
            self::$tbn[$n++] = 'CFG_AREAS';
            self::$tbn[$n++] = 'CFG_AREAS_USERS';
            self::$tbn[$n++] = 'CFG_AREAS_GROUPS';
            self::$tbn[$n++] = 'CFG_AREAS_GROUPS_USERS';
            self::$tbn[$n++] = 'CFG_AREAS_APPS';
            self::$tbn[$n++] = 'CFG_AREAS_APPS_PERMS';
            self::$tbn[$n++] = 'CFG_AREAS_APPS_USERS';
            self::$tbn[$n++] = 'CFG_AREAS_APPS_GROUPS';
            self::$tbn[$n++] = 'CFG_AREAS_APPS_GROUPS_PERMS';
            self::$tbn[$n++] = 'CFG_AREAS_APPS_USERS_PERMS';

            self::$tbn[$n++] = 'TSK_TAGS';
            self::$tbn[$n++] = 'TSK_PRIORITIES';
            self::$tbn[$n++] = 'TSK_TYPES';
            self::$tbn[$n++] = 'TSK_STATES';
            self::$tbn[$n++] = 'TSK_TASKS';
            self::$tbn[$n++] = 'TSK_TASKS_TAGS';
            self::$tbn[$n++] = 'TSK_TASKS_USERS';
            self::$tbn[$n++] = 'TSK_TASKS_FILES';
            self::$tbn[$n++] = 'TSK_TASKS_ACTIONS';
            self::$tbn[$n++] = 'TSK_TASKS_CHECKLIST';

            self::$tbn[$n++] = 'CLI_CUSTOMERS';
            self::$tbn[$n++] = 'CLI_INVOICES';
            self::$tbn[$n++] = 'CLI_INVOICE_LINES';
            self::$tbn[$n++] = 'CLI_INVOICE_PAYMENTS';
            self::$tbn[$n++] = 'CLI_CUSTOMER_CONTACTS';
            self::$tbn[$n++] = 'CLI_DOMAINS';
        }
    }

    function __destruct() {
        //Messages::info(self::$cache_id.' MyCache saved');
    }
  
    public static function singleton(){
        if( self::$instance == null ){
            self::$instance = new self();

        }
        return self::$instance;
    }
    
    public static function log($s){
        if(CFG::$vars['db']['log']){
            global $log;
            $log->data[] = $s;
        }
    }

    public static function clean($str) {                               
        $str = str_replace('JOIN',',',$str);
        return str_replace(['IGNORE','DELAYED','HIGH_PRIORITY','LOW_PRIORITY','RIGHT','LEFT','OUTER','INNER','QUICK',"\\", "\x00","\n", "\r", "'",'"',"\x1a",'`','(',')'],'',$str);
    }

    public static function getTableNameFromSql($sql){
        $words = explode(' ',self::clean($sql));
        $found = false;
        foreach ($words as $word){
            if ($word!=''){
                if ($found) {            
                    if     (self::$mode==1) return self::table2num( trim($word));   // Table idenfifiers as: 1 Numbers, 2 CRC's, 3 Names
                    else if(self::$mode==2) return self::table2crc( trim($word));
                    else if(self::$mode==3) return trim($word);    
                }
                if (in_array($word,['INTO','UPDATE','FROM','TABLE'])) $found = true;
            }
        }
    }

    private static function table2crc($tablename){
        //return hash('adler32', $tablename);           //crc32b
        //return crc32(json_encode($tablename));
        return crc32($tablename);
    }

    private static function table2num($tablename){
        foreach (self::$tbn as $k=>$v){
            if($v==$tablename) return $k;
        }
        return $tablename; //self::table2crc($tablename);
    }

    public static function getTableNumbers($sql){
    }

    public static function TEST_getTableNamesFromSelect($sql){  
        $words = explode(' ', self::clean($sql));
        $in_from = false;
        $from = '';
        foreach ($words as $word){
            if ($word!=''){
                //if (in_array($word,['RIGHT','LEFT','OUTER','INNER','JOIN'])) continue;
                if (in_array($word,['WHERE','ORDER','GROUP','LIMIT','HAVING','AND','ON','WINDOW','FOR','INTO','PARTITION'])) {
                    $in_from = false;
                    $from .= ';';
                }
                if ( $in_from) $from .= ' '.$word; 
                if (strtoupper($word)=='FROM') {
                    $in_from = true;
                }
                echo $in_from 
                   ? '<span style="color:green;font-weight:600;">['.$word.']</span><br>'
                   : '<span style="color:silver;font-weight:100;">['.$word.']</span><br>';
            }
        }

        $tablenames = array();
        $parts = explode(';',$from);    
        foreach($parts as $part){
            $tables = explode(',',$part);
            foreach ($tables as $table){
                if($table) {
                    Vars::debug_var(explode(' ',trim($table))[0],'TABLE');
                    if     (self::$mode==1) $tablenames[] = self::table2num(explode(' ',trim($table))[0]);   // Table idenfifiers as: 1 Numbers, 2 CRC's, 3 Names
                    else if(self::$mode==2) $tablenames[] = self::table2crc(explode(' ',trim($table))[0]);
                    else if(self::$mode==3) $tablenames[] = explode(' ',trim($table))[0]; 
                }
            }
        }
        return array_unique($tablenames);
    }
 
    public static function getTableNamesFromSelect($sql){
        $words = explode(' ', self::clean($sql));
        $in_from = false;
        $from = '';
       foreach ($words as $word){
            if ($word!=''){
                if (in_array($word,['WHERE','ORDER','GROUP','LIMIT','HAVING','AND','ON','WINDOW','FOR','INTO','PARTITION'])) {
                    $in_from = false;
                    $from .= ';';
                }
                if ( $in_from) $from .= ' '.$word; 
                if (strtoupper($word)=='FROM') $in_from = true;
            }
        }
        $tablenames = array();
        $parts = explode(';',$from);    
        foreach($parts as $part){
            $tables = explode(',',$part);
            foreach ($tables as $table){
                if($table) {
                    if     (self::$mode==1) $tablenames[] = self::table2num(explode(' ',trim($table))[0]);   // Table idenfifiers as: 1 Numbers, 2 CRC's, 3 Names
                    else if(self::$mode==2) $tablenames[] = self::table2crc(explode(' ',trim($table))[0]);
                    else if(self::$mode==3) $tablenames[] = explode(' ',trim($table))[0]; 
                }
            }
        }
        return array_unique($tablenames);
    }

    public static function keyFromSqlSelect($sql){
        if(strpos($sql,'SELECT')===false) return false;
        $str_tables = implode(self::$sep,self::getTableNamesFromSelect($sql));
        if($str_tables =='') $str_tables = '-'.str_replace(["\r","\n"],[' ',' ',],$sql).'-';
        //$key = str_replace(["\r","\n"],[' ',' ',],$sql).self::$ext;
        $key = hash('adler32',$sql). self::$sep . $str_tables  ;  //md5
        return $key;
    }

    public static function updateOnExec($sql,$debug=false){
        self::log( '<span class="DELETE">DELETE: [updateOnExec] :: '.self::clean(mb_strimwidth($sql, 0, 150, "...")).'</span>');
        $tablename = self::getTableNameFromSql($sql);
        if($debug) echo 'tablename:'.$tablename."\n";
        $mask = self::$prefix.'*'.$tablename.'*'.self::$ext;

        if(self::$redis){

            self::$redis_server->delete('*'.$tablename.'*');

        }else{

            if($debug) echo 'mask:'.CACHE_DIR.'/'.$mask."\n";
            foreach (glob(CACHE_DIR.'/'.$mask) as $filename) {
                if($debug) echo 'FILE:'.$filename."\n";
                //self::log( '<span class="DELETE">DELETE: [filename] :: '.$filename.'</span>');
                if(unlink($filename)){
                    if($debug) self::log( '<span class="DELETE">DELETE: [OK] :: '.$filename.'</span>');
                }else{
                    if($debug) self::log( '<span class="DELETE">DELETE: [ERROR] :: '.$filename.'</span>');
                }
            }

        }

    }

    public static function set($key,$data) {
        $data = serialize($data);

        if(self::$redis){

            self::$redis_server->set($key,$data);

        }else if(self::$memvar){
            //$key = keyToNum($key);
            $m = new MemVar($key,$data);
            $m->setValue($key,$data);
            $m->close();
        }else{
            $filename = CACHE_DIR.'/'.self::$prefix.$key.self::$ext;
            //self::debug($key);
            file_put_contents($filename, $data, LOCK_EX);
        }
        return $data;
        /*
        if($f = fopen($filename,'w+')) {
            if(@fwrite($f,$data)) {
                @fclose($f);
                return $data;
            } else Messages::error("MyCache->set::No se puede escribir en el archivo ".$filename.'<br />');
        } else Messages::error("MyCache->set::No se puede abrir el archivo ".$filename.'<br />');
        */
    }

    public static function get($key) {
        if(self::$redis){

            $data = unserialize(self::$redis_server->get( $key ));

        }else if(self::$memvar){
            //$key = keyToNum($key);
            $m = new MemVar($key);
            $data = unserialize($m->getValue( $key ));
            $m->close();
            return $data;
        }else{
            $filename = CACHE_DIR.'/'.self::$prefix.$key.self::$ext;
            if (file_exists($filename)) 
                return unserialize(file_get_contents($filename));
        }
        /***
        $filename = CACHE_DIR.'/'.self::$prefix.$key.self::$ext;
        if (file_exists($filename) && time() - self::$cachetime < filemtime($filename)) {
            return unserialize(file_get_contents($filename));
        }else{
            @unlink($filename);
            return false;
        }
        ***/
    }

    /*
    function getTempDir(){
        $temp = array();
        $temp[] = getenv('temp');
        $temp[] = sys_get_temp_dir();
        $temp[] = ini_get('upload_tmp_dir');
        foreach($temp as $key => $value){
            $temp[$key] = rtrim(rtrim($value,'\\'),'/');
        }
        return array_values(array_filter($temp));
    }
    */
    

    
}