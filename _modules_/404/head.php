<?php

    if($_ARGS[1]){
        $_page_name = Str::sanitizeName($_ARGS[1]);
        $found_rows = Table::sqlQuery("SELECT * FROM CLI_PAGES WHERE item_name= '".$_page_name."'");
        if(count($found_rows)==1){
            Header('Location: /page/'.$_page_name);  //FIX: change page by default module
        }
    }
