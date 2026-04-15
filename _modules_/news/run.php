
<div class="inner" id="zinner"><!--<div class="hr"></div>-->
<?php

    //Table::init();

    if($_SHOW_ONE){

        include(SCRIPT_DIR_MODULES.'/news/item.php');

    }else if($_SHOW_ALL){

        if(defined(TB_NAME.'_TOP')){
        if ( CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']=='es'){ 
            $field_name_TITLE = 'N.'.TB_PREFIX.'_TITLE'; 
            $field_name_SUBTITLE = 'N.'.TB_PREFIX.'_SUBTITLE'; 
            $field_name_NAME  = 'N.'.TB_PREFIX.'_NAME'; 
        }else{
            $field_name_TITLE = "COALESCE(NULLIF(N.".TB_PREFIX."_TITLE_".$_SESSION['lang'].",''), N.".TB_PREFIX."_TITLE) AS ".TB_PREFIX."_TITLE";
            $field_name_SUBTITLE = "COALESCE(NULLIF(N.".TB_PREFIX."_SUBTITLE_".$_SESSION['lang'].",''), N.".TB_PREFIX."_SUBTITLE) AS ".TB_PREFIX."_SUBTITLE";
            $field_name_NAME = "COALESCE(NULLIF(N.".TB_PREFIX."_NAME_".$_SESSION['lang'].",''), N.".TB_PREFIX."_NAME) AS ".TB_PREFIX."_NAME";
        }
        if(CFG::$vars['db']['type']=='sqlite')
            $sql_high = "SELECT ".$field_name_NAME.",".$field_name_TITLE.",".$field_name_SUBTITLE.",'./media/".TB_NAME."/files/'||F.".TB_NAME."_ID||'/'||F.FILE_NAME AS FILE_NAME FROM ".TB_PREFIX."_".TB_NAME." N, ".TB_PREFIX."_".TB_NAME."_FILES F WHERE N.".TB_PREFIX."_TOP='1' AND F.".TB_NAME."_ID =N.".TB_PREFIX."_ID AND F.MAIN='1'";
        else
            $sql_high = "SELECT ".$field_name_NAME.",".$field_name_TITLE.",".$field_name_SUBTITLE.",concat('./media/".TB_NAME."/files/',F.".TB_NAME."_ID,'/',F.FILE_NAME) AS FILE_NAME FROM ".TB_PREFIX."_".TB_NAME." N, ".TB_PREFIX."_".TB_NAME."_FILES F WHERE N.".TB_PREFIX."_TOP='1' AND F.".TB_NAME."_ID =N.".TB_PREFIX."_ID AND F.MAIN='1'";
        $row_high = Table::sqlQuery($sql_high);

        if($items_translated&&count($items_translated)>0)
            $mm = str_replace( array_values($items_translated[$_SESSION['lang']]), array_keys($items_translated[$_SESSION['lang']]), MODULE);
        else
            $mm = MODULE;

        ?>
        </div>
        <div id="<?=TB_NAME?>_TOP">
            <div class="inner"><a href="<?=Vars::mkUrl($mm,$row_high[0][TB_PREFIX.'_NAME'])?>"><span><?=$row_high[0][TB_PREFIX.'_SUBTITLE']?$row_high[0][TB_PREFIX.'_SUBTITLE']:$row_high[0][TB_PREFIX.'_TITLE']?></span></a></div>
            <?php if($row_high){ ?><img src="<?=$row_high[0]['FILE_NAME']?>"><?php } ?>
        </div>
        <div class="inner" id="inner">
        <?php
        }
   
        if (CFG::$vars['module'][MODULE]['header']){

            echo CFG::$vars['module'][MODULE]['header'];

        }else{

            echo '<h1>'.t(TB_NAME).'</h1>';

        }
        /**/
        if($_SHOW_LINK_ALL){
            ?>  
            <!--<script src="<?=SCRIPT_DIR_JS?>/jquery/jquery.tablednd.min.js?ver=1.1"></script>-->
            <a style="position:absolute;right:15px;top:-10px;" class="btn btn-small" href="<?=Vars::mkUrl(MODULE.'/view/'.($_SESSION[TB_PREFIX.'_'.TB_NAME]['view']=='all'?'':'all'))?>"><?=$_SESSION[TB_PREFIX.'_'.TB_NAME]['view']=='all'?'Ver activas':'Ver todo'?></a>
            <?php
        }
        /**/
       // include_once(SCRIPT_DIR_LIB.'/pdf/pdf_viewer.php'); 

        Table::show_table(TB_PREFIX.'_'.TB_NAME);

    }else if($_SHOW_404){

        ?><div id="div-404"></div><?php
        //ajax_load_url('404'); 

    }else{

        // CFG::$vars['widget']['404']=true;
        //echo '404';

    }
    // echo Table::recycleIcon();
    // echo Table::ajaxLoader();

?>
</div>


