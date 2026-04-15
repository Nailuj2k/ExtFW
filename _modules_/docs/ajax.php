<?php


    if($_ARGS['op'] && $_ARGS['op']=='savefile'){

        $result = array();  
        $result['error'] = 0;

        //$file = $_FILES['image']['tmp_name'];


        $result['msg']   = 'datos recibidos: '.$file; //.print_r($_ARGS,true);      

        define('UPLOAD_DIR', SCRIPT_DIR_MEDIA.'/epub');
        if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777);

        $file_ext = Str::get_file_extension($_FILES['image']['name']);
        //$arr_file_types = ['image/png', 'image/jpeg','application/octet-stream']; 
        //$accepted_img_extensions  = ['jpg','jpeg','png'];
        //$valid_file_ext = (    in_array($_FILES['image']['type'], $arr_file_types)                               // application/x-msdownload (exe)
        //                    && in_array($file_ext, $accepted_img_extensions)         );
        //if ($valid_file_ext){ 

            $hash =  date('Ymdhi').'_'.hash('crc32b',$_FILES['image']['tmp_name']); //hash_file('md5', $_FILES['image']['tmp_name']);
            $file_name = $_ARGS['filename']; //$hash./*'_'.Str::sanitizeName(basename($_FILES['image']['name']),true).*/'.webp';  //.'.'.$_FILES['image']['type'];
            $uploaded_file = UPLOAD_DIR  .'/'. $file_name;
             
            $result['msg'] = $_FILES['image']['tmp_name'].' == '.$file_name;

            if (file_exists($uploaded_file)) {
                $result['error'] = __LINE__;
                $result['msg'] = t('error_file_exists').': '.$file_name;

            }else{
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploaded_file)) {
                    if (file_exists($uploaded_file)) {
                        $result['uploaded_file']=$uploaded_file;
                    }
                }
            } 
        //}else{

        //    $result['msg'] = 'Invalid ext: '.$_FILES['image']['name'].' -> '.$file_ext;

        //}




      $result['filename']= $_ARGS['filename'];










        //$_encrypted_text = $_ARGS['str_image'];
        //$_decrypted_text = Crypt::crypt2str($_encrypted_text,$_SESSION['token']);
        /***
        if($_decrypted_text === NULL){

            $result['error'] = 1;
            $result['msg'] = t('TOKEN_HAS_EXPIRED_PLEASE_RELOAD_SESSION');

        }else{

            $result['msg'] = 'Data received';
            $text = $_decrypted_text;
        }
        **/

        //$result['image']  = strlen($_ARGS['image']);

        /*
                    $ifp = fopen(SCRIPT_DIR_MODULE.'/'.'image.png', "wb"); 
                    $data = explode(',', $_ARGS['image']);
                    fwrite($ifp, base64_decode($data[1])); 
                    fclose($ifp); 


                    $result['img']   = $data[1];


                    $result['url'] = 'https://tienda.extralab.net/_modules_/docs/image.png?v='.time();
         */


        echo json_encode($result);


    }else{

        include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');

    }