<?php

    //echo $this->format_item['begin']; 
    //Vars::debug_var($_item_tags);
    //Vars::debug_var($_item_values);
    echo str_replace($_item_tags, $_item_values, $this->format_item['begin']);
