<?php 

$TYPE = $_ARGS['type']?$_ARGS['type']:'1';

if($TYPE=='1'){

    if (in_array($_ARGS[1],array_merge(['view','theme','lang','output','debug','control_panel','login'],CFG::$vars['outputs'],CFG::$vars['langs'])) )  $_ARGS[1]=false; 


    if ($_ARGS[1]){

        $SCRIPT_DIR_MODULE_MEDIA='media/page/files';
        $field_text_name = $_SESSION['lang']=='es'?'item_text':'item_text_'.$_SESSION['lang'];
        $field_title_name = $_SESSION['lang']=='es'?'item_title':'item_title_'.$_SESSION['lang'];

        $rows = Table::sqlQuery("SELECT * FROM ".TB_PAGES." WHERE item_name= '".Str::sanitizeName($_ARGS[1])."'");

        if(count($rows)==1){
           
            $item_id = $rows[0]['item_id']; 
            $item_text = $rows[0][$field_text_name];
            if($_SESSION['lang']!='es' && !$item_text) $item_text = $rows[0]['item_text'];
            $item_text = str_replace('"'.$SCRIPT_DIR_MODULE_MEDIA,'"/'.$SCRIPT_DIR_MODULE_MEDIA,$item_text);

            $item_text = str_replace(['\n','\"'],['','"'], $item_text);
            
            echo $item_text;

            //echo image2ascii('media/page/files/99/.tn_294d6903dfe014a2d88672c37b270e2bb5432b58.jpg');
            //echo image2ascii( SCRIPT_DIR_MEDIA.'/test5.png' ); 

        }elseif( count($rows)<1 && $_ACL->HasPermission('edit_items') ){

            ?>
            <h3>404 Página no encontrada</h3>
            <p>La página <b><?=$_ARGS[1]?></b> no existe</p>
            <?php 

        }

    }else{

        echo '<h2>Páginas</h2>';
        $rows = Table::sqlQuery("SELECT * FROM ".TB_PAGES);
        foreach($rows as $row){
            echo '<a href="'.$row['item_name'].'">'.$row['item_title'].'</a><br />';     
        }

    }

}else{

   include(SCRIPT_DIR_MODULES.'/page/inline_widget.php');

}