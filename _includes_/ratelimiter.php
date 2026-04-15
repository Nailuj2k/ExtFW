<?php

if(isset($cfg['ratelimiter']['enabled']) && $cfg['ratelimiter']['enabled']===true){
    /*
        include(SCRIPT_DIR_CLASSES.'/storage/storage.interface.php');
        //include(SCRIPT_DIR_CLASSES.'/storage/sqlite.storage.class.php');
        include(SCRIPT_DIR_CLASSES.'/storage/mysql.storage.class.php');
        include(SCRIPT_DIR_CLASSES.'/ratelimiter.class.php');
    */

    //$redis = new Redis();
    //$redis->connect('127.0.0.1', 6379);
    //storage = new RedisStorage($redis);

    //$storage = new SQLiteStorage('cache.sqlite');

    $storage = new MySQLStorage(MySql_PDO::singleton());

    $rateLimitter = new RateLimiter([
        'refillPeriod' => 60, //CFG::$vars['ratelimiter.refillperiod'],  //60,
        'maxCapacity' => 60, //CFG::$vars['ratelimiter.maxcapacity'],    //5,
        'prefix' => 'api'
    ], $storage);

    $ip = $_SERVER['REMOTE_ADDR'];

    if ($rateLimitter->check($ip)) {

    } else {
        
        header('HTTP/1.1 429 Too Many Requests');
        //echo 'Rate limit exceeded. Try again later.';
        die('<p class="warning">Rate limit exceeded. Try again later.</p>');
        exit();        

    }
   

}

