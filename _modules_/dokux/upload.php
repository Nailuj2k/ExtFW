<script type="text/javascript">
  var upload_modulename = '<?=MODULE?>';
  var upload_fieldname  = 'FILE_NAME';
  var upload_if_exists  = false;
</script>

<?php 
$_upload_debug = true;
$_upload_demo = false;
$_upload_max_files = 25;
$_upload_max_file_size = 30;
//$_upload_mod_name = MODULE; 
?>

<script type="text/javascript">
 var msg_browsernotsupported= '<?=t('Su navegador no es compatible esta función. Necesita HTML5')?>';
 var msg_toomanyfiles        = '<?=sprintf(t('¡Demasiados archivos. Por favor, seleccione %s como máximo!'),$_upload_max_files)?>';
 var msg_filetoolarge        = '<?=sprintf(t('El archivo {0} es demasiado grande. Por favor, seleccione archivos de %sMb máximo'),$_upload_max_file_size)?>';
 var msg_onlyimagesallowed  = '<?=t('Sólo se admiten imágenes!')?>';
</script>

<div id="dropbox_upload">
     <span class="message"><?=t('Arrastre documentos aquí para subirlos')?> <br /><i><?=t('Puede subir hasta 25 cada vez, con un tamaño máximo de MAX_SIZE Mb.')?></i></span>
     <a class="btn btn-danger" id="btn-close-dropbox" style="position:absolute;top:4px;right:4px;text-align:center;cursor:pointer;"><i class="fa fa-close fa-inverse"></i></a>
</div>