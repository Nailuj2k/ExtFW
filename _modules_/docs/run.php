<link  href="<?=SCRIPT_DIR_MODULE?>/style.css?ver=1.0.8" rel="stylesheet" type="text/css" />

<script>
    var widget_loaded = false;
</script>

<div class="inner">

<?php

    $documents = true;

    if ($_ARGS[1]=='tag'&&$_ARGS[2]){
        $_SESSION['DOC_FILES']['tag'] = $_ARGS[2]=='all'?false:$_ARGS[2];
        $_SESSION['DOC_FILES']['tag_name'] = $_ARGS[2]=='all' ? false : $t::getFieldValue ('SELECT CAPTION FROM DOC_TAGS WHERE NAME = \''.$_ARGS[2].'\'') ;
    }else if ($_ARGS[1]=='tag'){
        $_SESSION['DOC_FILES']['tag']=false;
    }

    //Table::init();
    Table::show_table('DOC_FILES',false,true,1,true);   //show_table($tablename,$modulename=false,$element=true,$page_number=1,$sortable=false)

    if(Administrador()){
        ?><div id="pages_files_cfg" style="display:none;"><?php
        Table::show_table('DOC_CATEGORIES');     
        Table::show_table('DOC_TAGS');     
        Table::show_table('DOC_FILES_TAGS');     
        ?></div><?php
    }

?>
</div>
