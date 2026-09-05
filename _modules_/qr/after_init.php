<?php

  
    if (isset($_ARGS[1]) && $_ARGS[1]!=='ajax'){

        Table::init();

        $from_url = Str::sanitizeName($_ARGS[1]);

        $rows = Table::sqlQuery("SELECT * FROM CLI_QRCODES WHERE FROM_URL= '".$from_url."'");

        if(count($rows)==1){

            $to_url = $rows[0]['TO_URL'];

            header('Location: '.$to_url);

        }else{

            Header('Location: /404');

        }

    }else{

        // Header('Location: /404');

    }
