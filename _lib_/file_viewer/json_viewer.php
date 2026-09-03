<?php
$btn_maxmin = '<i style="cursor:pointer;float:right;margin:2px 5px;" id="json_viewer_maximize" title="Restaurar" class="fa fa fa-window-restore fa-inverse"></i>';
$btn_close = '<i style="cursor:pointer;float:right;margin:2px 5px;" title="Cerrar" class="fa fa-close close-box fa-inverse"></i>';
?>

<div id="dialog-file-json" class="extfw_ware_box shadow">
    <div class="title-bar">
        <span>Texto JSON</span>
        <?=$btn_close.$btn_maxmin?>
        <a data-class="READ"   class="btn-link READ">READ</a>
        <a data-class="WRITE"  class="btn-link WRITE">WRITE</a>
        <a data-class="DELETE" class="btn-link DELETE">DELETE</a>
        <a data-class="FAIL"   class="btn-link FAIL">FAIL</a>
    </div>
    <div id="dialog-file-text">
    </div>
</div>