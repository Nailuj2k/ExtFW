<?php

    echo  CFG::$vars['templates']['view']['header'];
    //echo $this->format_item['begin']; 
    echo str_replace($_item_tags, $_item_values, $this->format_item['begin'] ?? '');
