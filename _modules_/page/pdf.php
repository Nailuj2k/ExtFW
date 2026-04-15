<?php

    $rows = Table::sqlQuery("SELECT * FROM ".TB_PAGES." WHERE item_name= '".$_ARGS[1]."'");
    
    if(count($rows)==1){

         //echo CFG::$vars['templates']['pdf']['header']; 
         echo $rows[0]['item_text']; 
         //echo CFG::$vars['templates']['pdf']['footer']; 

    }elseif(count($rows)<1){
         
         echo '...';

    }
