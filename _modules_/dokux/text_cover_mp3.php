<h1>Test Cover mp3</h1>
<?php





        $ajax_result['thumbs']=array();
        include SCRIPT_DIR_LIB.'/simplehtmldom/simple_html_dom.php';
        //$_ARGS['search']='henry mancini peter gunn';
      //  $search =  str_replace(' ','+',$_ARGS['search']); //str_replace(' ','+',trim(HULAMM_ware::read_file_text( HULAMM_WARE_INBOX_OCR.'/'.$_ARGS['hash'].'.txt')));
        $url_search = 'https://www.google.com/search?q=+Going+To+California+Led+Zeppelin+IV+Led+Zeppelin&tbm=isch';  // "https://www.google.com/search?q={$search}&tbm=isch"; //
        $ajax_result['url_search'] = $url_search;
        
        $html = file_get_html($url_search);
        $images = $html->find('img'); //.t0fcAb');
        $image_count = 10;
        $i = 0;
        foreach($images as $image){
            if($i == $image_count) break;
            $i++;
            if($i < 2) continue;
            $ajax_result['thumbs'][]=$image->src;
            $html->clear();
        }
        
        // https://intranet.hulamm.sms.carm.es/hulamm_ware/ajax/op=getthumbs/hash=08888af5f6b93e10154fa5d4e5ffea92ea3a2ab4
        //Vars::debug_var($ajax_result['thumbs']);
        echo json_encode($ajax_result);