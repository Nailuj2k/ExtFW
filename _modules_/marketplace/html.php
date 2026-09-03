<?php


if($_ARGS['key']=='12345'){

     
   //BEGIN links
    $sql = "SELECT m.*,f.FILE_NAME FROM MKP_MARKETPLACE m, MKP_MARKETPLACE_FILES f 
            WHERE m.ID=f.ITEM_ID AND f.MINI='1' AND m.STATE='1' 
            ORDER BY m.LAST_UPDATE DESC";   

    $rows = Table::sqlQuery($sql); 
    $types = ['1'=>'module','2'=>'theme','3'=>'system'];
    

    $t = new Template;
    $t->set_root(SCRIPT_DIR_MODULE);
    $t->set_unknowns('remove');
    $t->set_file('page.html', 'out');

    $t->set_var('URL'   , $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] . SCRIPT_DIR );

    //$t->set_var('TITLE'   , 'Instrucciones'); //($htmltitle)?$htmltitle:'Foros');
    //$t->set_var('TEXT'    , 'Bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla bla');
      $t->set_var('INSTALL' , t('INSTALL','Instalar') );

    foreach ($rows as $row){

        $_type = $types[$row['TYPE']];
        //if($_type=='system')   
        //    $link = SCRIPT_DIR.'/control_panel/ajax/update/update/host='.str_replace('https://','',$row['REPO']);       
        //else 
            $link = SCRIPT_DIR.'/control_panel/ajax/update/'.$_type.'/'.$row['NAME'].'/host='.str_replace('https://','',$row['REPO']).'/key='.time();       
        /*
        if ($_type=='module'){
            $link_data  = SCRIPT_DIR.'/control_panel/ajax/update/data/'.$row['NAME'].'/host='.str_replace('https://','',$row['REPO']).'/key='.time();       
            $link_files = SCRIPT_DIR.'/control_panel/ajax/update/files/'.$row['NAME'].'/host='.str_replace('https://','',$row['REPO']).'/key='.time();       
        }
        */

        $t->set_var("id", $row['ID']);
        $t->set_var("title", $row['TITLE']);
        $t->set_var("repo", str_replace('https://','',$row['REPO']));
        $t->set_var("icon", $row['FILE_NAME']);
        $t->set_var("type", $_type);
        $t->set_var("link", $link);
        $t->set_var("fecha", $row['LAST_UPDATE']);
       // $t->parse("itemlinks");
        $t->parse("item");

    }

    $t->pparse("out");


}else{

    
    die('Invalid key');


}