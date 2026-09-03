<?php

    if ( isset($_ARGS['op']) && in_array($_ARGS['op'],['save_page'
                                                      ,'save_file'
                                                      ,'clone_page'
                                                      ,'image-crop'
                                                      ,'update_score'
                                                      ,'files-gallery']) ) {


        include(SCRIPT_DIR_MODULES.'/page/inline_ajax.php');      

        
    } else if ( isset($_ARGS['op']) && (str_contains($_ARGS['op'],'comment')
                                      ||str_contains($_ARGS['op'],'rating') 
                                      ||str_contains($_ARGS['op'],'invoice')
                                      ||str_contains($_ARGS['op'],'withdraw') ) ) {
        

        include(SCRIPT_DIR_MODULES.'/comments/ajax_comments.php');


    } else {


        include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');


    }