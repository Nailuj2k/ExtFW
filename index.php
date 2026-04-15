<?php

declare(strict_types=1);


/* * * * * * * * * * * * *
 *
 *   ExtFW Framework
 *   2010-2026 © Julián Torres
 *
 * * * * * * * * * * */

date_default_timezone_set(date_default_timezone_get());
$start_time = microtime(true);

require(__DIR__.'/_includes_/init.php'             );
require(__DIR__.'/configuration.php'               ); 
require( SCRIPT_DIR_CLASSES  . '/config.class.php' );
require( SCRIPT_DIR_INCLUDES . '/autoloader.php'   ); 
require( SCRIPT_DIR_INCLUDES . '/ratelimiter.php'  );
require( SCRIPT_DIR_INCLUDES . '/security.php'     );
require( SCRIPT_DIR_INCLUDES . '/functions.php'    );
require( SCRIPT_DIR_CLASSES  . '/dates.class.php'  );  
require( SCRIPT_DIR_CLASSES  . '/errors.class.php' );
require( SCRIPT_DIR_INCLUDES . '/run.php'          );
