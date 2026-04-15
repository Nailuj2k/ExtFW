<link  href="<?=SCRIPT_DIR_MODULE?>/style.css?ver=1.1.9" rel="stylesheet" type="text/css" />

<div class="inner">
<h1>Links</h1>
<?php  

if($_ARGS[1]=='admin' && $_ACL->hasPermission('links_edit')){

    Table::init();
    Table::show_table('CFG_LINKS'); 

}else{


    widget('links');

    if($_ACL->hasPermission('links_edit')){
        ?><p style="margin-right:-13px;border-top:1px solid #699ebe;margin-top:20px;padding-top:5px; text-align:right;"><a href="links/admin" class="btn btn-success">Administrar links</a></p><?php
    }

}

?>
</div>
