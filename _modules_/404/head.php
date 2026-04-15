<?php

    if($_ARGS[1]){
        $found_rows = Table::sqlQuery("SELECT * FROM CLI_PAGES WHERE item_name= '".$_ARGS[1]."'");
        if(count($found_rows)==1){
            Header('Location: /page/'.$_ARGS[1]);  //FIX: change page by default module
        }
    }
