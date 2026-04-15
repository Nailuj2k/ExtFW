<?php 
   
    include(SCRIPT_DIR_CLASSES.'/scaffold/ajax_file.php');

            /**************
            if     ($_ARGS['path']=='inbox') $path = HULAMM_WARE_INBOX_DIR;
            else if($_ARGS['path']=='ocr')   $path = HULAMM_WARE_INBOX_OCR;
            else if($_ARGS['path']=='files') $path = HULAMM_WARE_FILES_DIR;
            else if($_ARGS['path']=='thumb') $path = HULAMM_WARE_FILES_TMB;
            else if($_ARGS['path'])          $path = str_replace('_','/',$_ARGS['path']);
            else                             $path = '';
            *********/ 

               /**************
                $html_images_ext = array('jpg','jpeg','png','gif');
                $html_txt_ext = array('txt','php','html','htm','css');
                $ext = Str::get_file_extension($filename);
                $valid_img_ext = in_array($ext,$html_images_ext);
                $valid_txt_ext = in_array($ext,$html_txt_ext);
                $tmp_file = $path.'/'.$tnprefix.$filename;
                **************/
