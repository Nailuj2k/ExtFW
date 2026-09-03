<?php
if(1==1){
     // make session read-only
     //   session_start();
     //   session_write_close();

    // disable default disconnect checks
    ignore_user_abort(true);
    /***
    date_default_timezone_set("Europe/Madrid");
    header("Cache-Control: no-store");
    header("Content-Type: text/event-stream");
    **/
    // set headers for stream
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header("Content-Type: text/event-stream");
    header("Cache-Control: no-cache");

    /*************
    // Is this a new stream or an existing one?
    $lastEventId = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : 0);
    if ($lastEventId == 0) {
            $lastEventId = floatval(isset($_GET["lastEventId"]) ? $_GET["lastEventId"] : 0);
    }

    echo ":" . str_repeat(" ", 2048) . "\n"; // 2 kB padding for IE
    echo "retry: 2000\n";

    // start stream
    while(true){

            if(connection_aborted()){
                exit();
            }

            else{

                // here you will want to get the latest event id you have created on the server, but for now we will
                // increment and force an update
                $latestEventId = $lastEventId+1;

                if($lastEventId < $latestEventId)

                    echo "id: " . $latestEventId . "\n";
                    echo "data: Howdy (".$latestEventId.") \n\n";
                    $lastEventId = $latestEventId;
                    ob_flush();
                    flush();

                }

                else{
                
                    // no new data to send
                    echo ": heartbeat\n\n";
                    ob_flush();
                    flush();
                    
                }

    }
    
    // 2 second sleep then carry on
    sleep(2);
    *********/


    include(SCRIPT_DIR_MODULE.'/index.php');
}
/************* 
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

while (true) {
  	$data = [	'name' => 'Rasmus Lerdorf'  ];

  	echo "event: sse\n";
  	echo "data: " . json_encode($data) . "\n\n";
    echo str_pad('', 4096) . "\n";
      
	ob_flush();
  	flush();
  	sleep(1);

  	if (connection_aborted()) {
    	break;
  	}
}
ob_end_flush();
**/