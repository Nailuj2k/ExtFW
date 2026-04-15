<div class="inner inner-page"><?php

if($_HTML_duplicate_entry??false){
    
    echo $_HTML_duplicate_entry;
    
}else if($_SHOW_page){
    
                $_CLASS_= 'page-content';
                $_AVD_  = true; // MODULE=='page' && $items_rows[0]['HTML']!='1';

                include(SCRIPT_DIR_MODULES.'/page/inline_edit.php');
              
                if($row['ALLOW_RATING'])    Rating::show($_ID_);
                if($row['ALLOW_COMMENTS'])  Comments::show($_ID_); 

}elseif($_CREATE_page){

                    //Vars::debug_var($_ARGS,__LINE__);
                
                    $title = ucfirst(str_replace('_',' ',$_ARGS[1]));
                    $name = Str::sanitizeName($_ARGS[1]);

                    if ($_ARGS[3]){
                        if(CFG::$vars['db']['type']  == 'mysql'){
                            $from_id = (int)$_ARGS[3];
                            if ( is_int( $from_id ) ) {
                                Table::sqlExec("CREATE TEMPORARY TABLE temp_table AS SELECT * FROM ".TB_PAGES." WHERE item_id=".$from_id);
                                Table::sqlExec("UPDATE temp_table SET item_id=0,item_name='".$name."',item_title='".$title."' WHERE item_id=".$from_id);
                                Table::sqlExec("INSERT INTO ".TB_PAGES." SELECT * FROM temp_table");
                                Table::sqlExec("DROP TEMPORARY TABLE temp_table");
                                Jeader($_ARGS[0].'/'.$_ARGS[1]);
                            }else{
                                ?>WTF?? (<?=$_ARGS[3]?>)<?php 
                            }
                        }else{
                            echo '<p>'.t('OPTION_NOT_AVAILABLE_FOR','Opción no disponible para ').'<b> '.CFG::$vars['db']['type'].'</b><p>';
                        }
                    }else if(Str::is_friendly_name($name, 3, 100)){
                        $content =  str_replace('[TITLE]',$title,CFG::$vars['templates']['page']['default']);
                        $sql = "INSERT INTO ".TB_PAGES." ( item_title,item_name,item_level,item_parent,item_active,inline_edit,item_visible,item_order,item_text) 
                          VALUES ('".$title."','".$name."','100','0','1','1','1','0','".$content."')";
          
                         Table::sqlExec($sql);        //FIX Check for errors
               
                         Jeader($_ARGS[0].'/'.$_ARGS[1]);
                        }else{
                        echo '<p>'.t('INVALID_NAME','Nombre no válido').': '.$name.'<p>';
                    }

}elseif($_NOT_FOUND){

    if($_ACL->HasPermission('edit_items')){
        // $ext = Str::get_file_extension(str_replace('_','-',$_ARGS[1]));
        $new_pagename = Str::sanitizeName(str_replace('_','-',$_ARGS[1]));  
        ?>
        
        <h3>404 <?=t('PAGE_NOT_FOUND','Página no encontrada')?></h3>
        <p><?=t('SORRY_PAGE_NOT_FOUND','Lo sentimos, la página solicitada no existe o no se encuentra disponible.')?></p>


        <p><?=t('IF_YOU_WANT_TO_CREATE_IT_CLICK','Si desea crearla pulse')?> <a href="<?=$_ARGS[0].'/'.$new_pagename?>/create" style="color:#da2335;font-weight:bold;"> <?=t('THIS_LINK','éste enlace')?> </a></p>
        <h4> ... <?=t('OR_USE_EXISTING_AS_TEMPLATE','o bien puede usar alguna de las existentes como plantilla pulsando la que le interese:')?></h4>


        <style>a:hover{color:#0099ff;}</style>
        <?php
        $rows = Table::sqlQuery("SELECT * FROM ".TB_PAGES); //." WHERE item_name= '".$new_pagename."'");
        foreach($rows as $row){
            echo '<a href="'.$_ARGS[0].'/'.$new_pagename.'/create/'.$row['item_id'].'">'.$row['item_title'].'</a><br />';     
        }                
    }else{

        include(SCRIPT_DIR_MODULES.'/404/run.php');
        /*
        ?>
        <h3>404 <?=t('PAGE_NOT_FOUND','Página no encontrada')?></h3>
        <script type="text/javascript">
            //ready(function(){           
                fetch('/404/html')
                    .then(response => response.text())
                    .then(data => {
                       document.querySelector('.inner-page').innerHTML = data;
                    }); 
            //});
        </script>    
        <?php
       */
    }

}elseif( $_PAGE_LIST){

        echo '<h2>Páginas ['.$db_engine.']</h2>';

        //TEST Table::init();
        Table::show_table('CLI_PAGES');

}

?>

</div><!--inner-page-->


