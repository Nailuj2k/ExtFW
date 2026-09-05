<?php 
   
   //print_r($_ARGS);
   if ($doku_download) {

       if(     isset($_ARGS["filename"]) 
            && isset($_ARGS["ext"] )   ) {
            
            $ext = $_ARGS["ext"];
            $filename = $_ARGS["filename"];
            $name = $_ARGS['name']?$_ARGS['name']:$filename ;

            if     ($_ARGS['path']=='inbox') $path = DOKUX_INBOX_DIR;
            else if($_ARGS['path']=='ocr')   $path = DOKUX_INBOX_OCR;
            else if($_ARGS['path']=='files') $path = DOKUX_FILES_DIR;
            else if($_ARGS['path']=='thumb') $path = DOKUX_FILES_TMB;
            else if($_ARGS['path'])          $path = str_replace('_','/',$_ARGS['path']);
            else                             $path = '';

            $mode = $_ARGS['mode']=='inline'?$_ARGS['mode']:'' ;
            $filepath = $path . '/' . $filename . '.' . $ext;
            if(file_exists($filepath)) {
               
               // echo '<br />'.$filepath;
               
                /**************
                $html_images_ext = array('jpg','jpeg','png','gif');
                $html_txt_ext = array('txt','php','html','htm','css');
                $ext = Str::get_file_extension($filename);
                $valid_img_ext = in_array($ext,$html_images_ext);
                $valid_txt_ext = in_array($ext,$html_txt_ext);
                $tmp_file = $path.'/'.$tnprefix.$filename;
                **************/

                session_write_close ( ); 
              
                if      ($ext=='pdf') header('Content-Type: application/octet-stream');
                else if ($ext=='png') header('Content-Type: image/png' );
                else if ($ext=='jpg') header('Content-Type: image/jpeg' );
                else if ($ext=='webp') header('Content-Type: image/webp' );
                else if ($ext=='mp3') header('Content-Type: audio/mp3' );

                if($mode=='inline'){

                }else{
                    header('Content-Description: File Transfer');
                    header('Content-Disposition: attachment; filename="'.$name.'.'.$ext.'"');
                }
                
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                flush(); // Flush system output buffer
                readfile($filepath);
                // die();
                
            } else {

                // header('Location: /404');

            }

        } else {

           // header('Location: /404');

        }

}else{

   // header('Location: /404');
   echo 'NO';
}