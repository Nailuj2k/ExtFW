<?php


$tabla = new myTableMysql(TB_USER);

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_USERS.php');

//if ($tabla->profile) {
    $tabla->detail_tables=array();
    $tabla->detail_tables[] = 'CLI_USER_FILES';
    $tabla->detail_tables[] = 'CLI_USER_ADDRESSES';
    $tabla->detail_tables[] = 'CLI_USER_KEYS';
    $tabla->detail_tables[] = 'CLI_USER_TIMESTAMP';
//}

//$tabla->output='raw';



class myTableMysql extends TableMysql{

  public function customfilter($params){
    $filter = $params['filter']=='ALL' ? 0 : $params['filter'];
    $page = $params['page'];
    if    ($filter) {
      $_SESSION['_CACHE']['CLI_USER']['filterstring'] = ' user_id IN (SELECT id_user FROM '.TB_ACL_USER_ROLES.' WHERE id_role='.$filter.')';  
      $_SESSION['_CACHE']['CLI_USER']['filterindex'] =  $filter;  
    }else{
      $_SESSION['_CACHE']['CLI_USER']['filterstring'] = false;
      $_SESSION['_CACHE']['CLI_USER']['filterindex'] = false;
    }   
    $result = array();
    $result['error']=0;
    $result['msg'] = 'Filtro: '.$flt.', Page: '.$page;
    echo json_encode($result);
  }


  function base64_to_jpeg($base64_string, $output_file) {
    $ifp = fopen($output_file, "wb"); 
    $data = explode(',', $base64_string);
    fwrite($ifp, base64_decode($data[1])); 
    fclose($ifp); 
    return $output_file; 
  }

  function imagereceive($params) {

      // /control_panel/ajax/op=function/function=imagereceive/table=CLI_USER/id=4
    $result = array();
    $result['error']=0;
    $result['log'] ='';
    $type = $params['type'] ?? 'avatar';
         $_dirphotos = SCRIPT_DIR_MEDIA.'/avatars/';      //$this->colByName('user_url_avatar')->uploaddir;

               $maxFileSize = 2 * 1024 * 1024; // 5 MB  
               
               $allowedExtensions = ['jpg', 'webp', 'jpeg', 'png', 'gif'];


               if (isset($_FILES['croppedImage']) && $_FILES['croppedImage']['error'] === UPLOAD_ERR_OK) {
                    $result['src'] = explode('?',$_POST['src'])[0];
                    $file = $_FILES['croppedImage'];
                    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    $filePath = $type === 'banner' 
                              ?  SCRIPT_DIR_MEDIA.'/nostr/banners/banner_'.$params['id'].'.'.$fileExtension
                              : $_dirphotos . $params['id'].'.'.$fileExtension;


                    $result['src'] = $filePath ;
                    if ($file['size'] > $maxFileSize) {
                        $result['msg'] = 'El archivo es demasiado grande ('.$file['size'].' > '.$maxFileSize.'). El tamaño máximo es 5 MB.';
                    } else if (!in_array($fileExtension, $allowedExtensions)) {
                        $result['msg'] = 'Formato de archivo no permitido. Solo se permiten archivos JPG, PNG y GIF.';
                    } else if (!file_exists($file['tmp_name'])) {
                        $result['msg'] = 'No existe el archivo '.$file['tmp_name'];
                  //}else if (!is_writable('/media/NEWS/files/11'))
                  //    $result['msg'] = 'No se puede escribir en /media/NEWS/files/11';
                    } else if (move_uploaded_file($file['tmp_name'], $filePath)) {
                        $result['error'] = 0;
                        $result['msg']   = 'La imagen se ha subido correctamente. Ruta: ' . $filePath;
                        $result['image'] = $filePath;

                        $result['msg'] = '<img src="/'.$filePath.'?hash='.time().'">';  //ID: '.$params['id'].'<br /> SQL: SELECT * FROM '.$prefix.'_user WHERE user_id='.$params['id'].'<br />
                        $result['img']=$filePath.'?hash='.time();

                        // Only update user_url_avatar for avatar uploads, not banners
                        if ($type !== 'banner') {
                             $_SESSION['user_url_avatar']= $params['id'].'.'.$fileExtension;
                            $this->sql_query("UPDATE CLI_USER SET user_url_avatar='".$params['id'].'.'.$fileExtension."' WHERE user_id = ".$params['id']);
                        }
                       /// }

                    } else {
                        $result['msg']   = 'Hubo un error al subir la imagen. '.$filePath;
                    }                      
               } else {
                        $result['msg']   = 'No se recibió ninguna imagen o hubo un error en la subida.';
               }

     echo json_encode($result);

  }

  public function resizeimage($params){

    //  global $prefix,$foto,$parent;
    $result = array();
    $result['error'] = 0;
    //    $_dirphotos = '../../media/inventario/activos/fotos/'.$parent.'/';
    $_dirphotos = SCRIPT_DIR_MEDIA.'/avatars/';      //$this->colByName('user_url_avatar')->uploaddir;
    $img_avatar = $_dirphotos . $params['id'].'.jpg';  //area8_tarjeta_amarillo.jpg';
    $foto       = $_dirphotos . $params['id'].'.jpg'; //( file_exists( $_dirphotos.'.tn_'.$_img) ? '.tn_'.$_img : $_img );
    if( @is_array(getimagesize($foto)) )  {
          $new_size  = $this->new_size( $foto, 316*4, 316*4);  //212  277
          $image_type  = $new_size['type'];
          $orig_width  = $new_size['ow'];
          $orig_height = $new_size['oh'];
         //          if($orig_width < $orig_height) {       
          if( $orig_width < 316 || $orig_height < 316) {       
              $new_width   = $new_size['nw'];
              $new_height  = $new_size['nh'];
              $profileim = imagecreatetruecolor($new_width,$new_height);
              switch ($image_type){
                 case 1: 
                    $profileim = imagecreatefromgif($foto); 
                    imagegif( $profileim, $img_avatar , 99); 
                    break;
                 case 2: 
                    //$profileim = imagecreatefromjpeg($foto);
                    imagecopyresized($profileim, imagecreatefromjpeg($foto), 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height); 
                    imagejpeg( $profileim, $img_avatar , 99);
                    /*******/
                          unlink($img_avatar);
                          while (!file_exists($img_avatar)) sleep(1);
                          include_once( SCRIPT_DIR_CLASSES.'/scaffold/lib/FaceDetector.php');
                          $face_detect = new Face_Detector(SCRIPT_DIR_CLASSES.'/scaffold/lib/detection.dat');
                          $face = $face_detect->face_detect($img_avatar);
                          if($face){
                            //$face_detect->toJpeg();
                            //$face_detect->cropFace();
                            imagejpeg( $face_detect->cropFace(), $img_avatar, 99);
                          }
                    /****/
                    break;
                 case 3: 
                    $profileim = imagecreatefrompng($foto);  
                    imagepng( $profileim, $img_avatar , 99);
                    break;
                 default:  
                   trigger_error('Unsupported filetype!', E_USER_WARNING);
                   break;
              }
              imagedestroy($profileim);
              $result['error'] = 0;
              $result['msg'] = ' ¡Tamaño ampliado! '.$img_avatar.' w['.$new_width.'] h['.$new_height.'] <img src="'.$foto.'">';
          }else{
            $result['error'] = 1;
            $result['msg'] = 'Foto no válida: '.$foto;
          }
    }else{  
      $result['error'] = 1;
      $result['msg'] = 'No hay foto';
    }
    echo json_encode($result);     
  }


  public function deleteimage($params){
    $result = array();
    $result['error'] = 0;

    $_dirphotos = SCRIPT_DIR_MEDIA.'/avatars/';      //$this->colByName('user_url_avatar')->uploaddir;
    $foto       = $_dirphotos . $params['id'].'.jpg'; //( file_exists( $_dirphotos.'.tn_'.$_img) ? '.tn_'.$_img : $_img );
    if( @is_array(getimagesize($foto)) )  {
      if(unlink($foto)){
        $this->sql_query('UPDATE '.$this->tablename.' SET FOTO=\'\' WHERE ID_ACTIVO='.$params['id']);
        $result['msg'] = 'Foto eliminada';
      }else{
        $result['error'] = 1;
        $result['msg'] = 'Error al eliminar la imagen '.$foto;
      }
    }else{  
      $this->sql_query('UPDATE  '.$this->tablename.' SET FOTO=\'\' WHERE ID_ACTIVO='.$params['id']);
      $result['error'] = 1;
      $result['msg'] = 'No hay foto';
    }
    echo json_encode($result);     
  }


}



