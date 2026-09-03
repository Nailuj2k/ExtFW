<?php 
if($_ACL->userHasRoleName('Root')){      // $_ACL->HasPermission('view_logs')


   //$_LOG_DIR  = CACHE_DIR; //'_modules_/products/pedidos/';


    // <pre style="background-color:#fefefe;padding:4px;border:1px solid gray;max-height:500px;width:100%;overflow:auto;"></pre>
    ?>
    <div id="log-options" style="margin-top:10px;text-align:right;">
        <a data-op="load"         class="btn-log btn">View log files</a> 
        <a data-op="load-cache"   class="btn-log btn">View cache files</a> 
        <a data-op="delete-log"   class="btn-log btn btn-danger">Delete log files</a> 
        <a data-op="delete-cache" class="btn-log btn btn-danger">Delete cache files</a>
    </div>
    <pre id="logs-files" class="file-list">
    </pre>

<style>
h4{color:#881316;}
h4 a{color:#1471a3;}
h4 a:hover{color:#51b5ea;}
h4,
h4 a{font-size:16px;font-family:Consolas,monospace;}
.file-list{background-color:#2c466d;max-height:500px;height:500px;overflow:auto;width:100%;padding:4px;border:1px solid gray;line-height:1.3em;}
.file-list,
.file-list span,
.file-list a{font-size:10px;font-family:Consolas,monospace;color:#fafafa;}
.file-list a img{height:16px;margin:0;}
.file-list .file-date{color:#95d280;}
.file-list .file-size{color:#faa0e1;}
#dialog-file-json{background-color:#2c466d !important;}
</style>

<?php 
}else{
    ?>Accesso denegado.<?php
}
?>
</div>
