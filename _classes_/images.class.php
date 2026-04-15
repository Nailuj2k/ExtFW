<?php

    function smart_resize_image( $file, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false ) {
        if ( $height <= 0 && $width <= 0 ) return false;
        $info = getimagesize($file);
        $image = '';
        $final_width = 0;
        $final_height = 0;
        
        list($width_old, $height_old) = $info;  //FIX divide by zero
        if($width_old<1 || $height_old<1) return false;

        if ($proportional) {
            if ($width == 0) $factor = $height/$height_old;
            elseif ($height == 0) $factor = $width/$width_old;
            else $factor = min ( $width / $width_old, $height / $height_old);  
            $final_width = round ($width_old * $factor);
            $final_height = round ($height_old * $factor);
        } else {
            $final_width = ( $width <= 0 ) ? $width_old : $width;
            $final_height = ( $height <= 0 ) ? $height_old : $height;
        }
        switch ( $info[2] ) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($file);
                break;
            default:
                return false;
        }
        $image_resized = imagecreatetruecolor( $final_width, $final_height );
        //FIX if(ANI_GIF)
        if ( ($info[2] == IMAGETYPE_GIF) && (strpos($file,'.ani.')!==false)) {

            // Animated gif FAIL !!

        }else if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)  || ($info[2] == IMAGETYPE_WEBP) ) {
            $trnprt_indx = imagecolortransparent($image);
            if ($trnprt_indx >= 0) {
                $trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
                $trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                imagefill($image_resized, 0, 0, $trnprt_indx);
                imagecolortransparent($image_resized, $trnprt_indx);
            } elseif ($info[2] == IMAGETYPE_PNG  || ($info[2] == IMAGETYPE_WEBP)) {
                imagealphablending($image_resized, true);
                $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                imagefill($image_resized, 0, 0, $color);
                imagesavealpha($image_resized, true);
            }
        }

        // if(CFG::$vars['images']['keep_originals'] &&  $width_old > $final_width){
        //  if (!file_exists($dir.'/'.BIG_PREFIX.$imagename))  copy($dir.'/'.$imagename, $dir.'/'.BIG_PREFIX.$imagename);
        // }

        imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
        
        if ( $delete_original ) {
            if ( $use_linux_commands )  exec('rm '.$file);
                                else  @unlink($file);
        }
        switch ( strtolower($output) ) {
            case 'browser':
                $mime = image_type_to_mime_type($info[2]);
                header("Content-type: $mime");
                $output = NULL;
                break;
            case 'file':
                $output = $file;
                break;
            case 'return':
                return $image_resized;
                break;
            default:
                break;
        }
        $img_quality = ( $info[2] == IMAGETYPE_JPEG || $info[2] == IMAGETYPE_WEBP ) 
                        ? ( CFG::$vars['images']['quality'] ? CFG::$vars['images']['quality'] : 100 )
                        : -1 ;

        switch ( $info[2] ) {
            case IMAGETYPE_GIF:
                imagegif($image_resized, $output);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image_resized, $output,$img_quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($image_resized, $output,-1); //9);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($image_resized, $output,$img_quality);
                break;
            default:
                return false;
        }
        return true;
    }

    function miniatura($dir, $imagename , $w=0 , $h=0 ) {  // 150 120
        $new_width  = ($w) ? $w : CFG::$vars['images']['max_thumbnail_w'];
        $new_height = ($h) ? $h : CFG::$vars['images']['max_thumbnail_h'];
        smart_resize_image( $dir.'/'.$imagename, $new_width, $new_height, true, $dir.'/'.TN_PREFIX.$imagename, false, false );
    }

    /*
    From: http://mediumexposure.com/smart-image-resizing-while-preserving-transparency-php-and-gd-library/
    From: http://github.com/maxim/smart_resize_image
    * If you pass width as 0 (zero) -- this function will disregard width, and use height as constraint. Same vice versa.
    * If you set "proportional" to false - the function will simply stretch (or shrink) the image to its full constraints.
    * If one of the dimensions is set to zero, and proportional set to "false" - then the image will be forced to stretch or shrink the other dimension, and disregard the zeroed dimension (leave it the same).
    * If proportional is set to true - the image will resize to constraints proportionally, once again, with possibility to have either width or height set to zero.
    * The function can use either linux "rm" command, or php @unlink. Most probably you don't need to ever use that flag, but on some setups - @unlink won't work due to user access restrictions.
    * The function will simply replace the file that you give it, with the resized file.
    * The function supports gif, png, and jpeg, and preserves the transparency of gif and png images.
    * Tested on GD version 2.0.28 only.
    */
    //function smart_resize_image( $file, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false ) {

    function img_resize($dir, $imagename, $new_width='', $new_height='') {  // 150 120
        $new_width  = ($new_width)  ? $new_width  : CFG::$vars['images']['max_image_w'];
        $new_height = ($new_height) ? $new_height : CFG::$vars['images']['max_image_h'];
        $thumbnail_size  = thumbnail_size( $dir.'/'.$imagename, $new_width, $new_height);
        $img_orig_width  = $thumbnail_size['ow'];
        $img_orig_height = $thumbnail_size['oh'];
        $img_new_width   = $thumbnail_size['w'];
        $img_new_height  = $thumbnail_size['h'];

        if(($img_orig_width < $new_width) && ($img_orig_height < $new_height)){
            Messages::warning('image does not need be resized: ['.$img_orig_width.' <= '.$new_width.' && '.$img_orig_height.' <= '.$new_height.']');
        }else{
            if(CFG::$vars['images']['keep_originals'] /* && !file_exists($dir.'/'.BIG_PREFIX.$imagename)*/)   copy($dir.'/'.$imagename, $dir.'/'.BIG_PREFIX.$imagename);
            smart_resize_image( $dir.'/'.$imagename, $new_width, $new_height, true, $dir.'/'.$imagename, true, false); //, $dir.'/'.BIG_PREFIX.$imagename );
        }
    }

    function png2webp($filename){
        if (!CFG::$vars['images']['webp']) return false;
        try {
            $im = imagecreatefrompng($filename);
            imagealphablending($im, true);
            imagesavealpha($im, true);
            $webp = imagewebp($im, str_replace('.png', '.webp', $filename), 100);
            imagedestroy($im);
        } catch (Exception $e) {
            return  $e->getMessage();
        } finally {
            return true;
        }


    }

    function jpeg2webp($filename){
        if (!CFG::$vars['images']['webp']) return false;
        $im = imagecreatefromjpeg($filename);
        $webp = imagewebp($im, str_replace(array('.jpg','.jpeg'), '.webp', $filename), 100);
        imagedestroy($im);
    }

    /* cms_functions */
    function LoadPNG($imgname){
        $im = @imagecreatefrompng($imgname); /* Attempt to open */
        if (!$im) { /* See if it failed */
            $im  = imagecreatetruecolor(150, 30); /* Create a blank image */
            $bgc = imagecolorallocate($im, 255, 255, 255);
            $tc  = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
            /* Output an errmsg */
            imagestring($im, 1, 5, 5, "Error loading $imgname", $tc);
        }
        return $im;
    }

    /* cms_image_list */
    function thumbnail_size( $img_original, $maxTbW,$maxTbH){
        $imageinfo = @getimagesize($img_original);
        if (!$imageinfo){
            Messages::warning('Imagen no encontrada: '.$img_original);
            return false;
        }
        $img_orig_width  = $imageinfo[0];
        $img_orig_height = $imageinfo[1];
        $img_type = $imageinfo[2];
        $res=array();
        if ( $img_orig_width <= $maxTbW && $img_orig_height <= $maxTbH){
            $res['w']=$img_orig_width;
            $res['h']=$img_orig_height;
            $res['ow']=$img_orig_width;
            $res['oh']=$img_orig_height;
        }else{
            //if ($img_orig_width<$img_orig_height) $maxTbH=round($img_orig_width * ($maxTbW / $img_orig_height));
            $mlt_w = $maxTbW / $img_orig_width;
            $mlt_h = $maxTbH / $img_orig_height;
            $mlt = $mlt_w < $mlt_h ? $mlt_w:$mlt_h;
            // calcular nuevas dimensiones
            $img_new_width = round($img_orig_width * $mlt);
            $img_new_height = round($img_orig_height * $mlt);
            //echo $img_new_width.' '.$img_new_height.'<br>';
            $res['w']=$img_new_width;
            $res['h']=$img_new_height;
            $res['ow']=$img_orig_width;
            $res['oh']=$img_orig_height;
        }	
        $res['type'] = $img_type;
        return $res;
    }


    function imageCreateFrom($image) {
        
        $pathinfo = pathinfo($image);  // '/www/htdocs/inc/lib.inc.php'   /media/fotos/images

        $ruta      = $pathinfo['dirname'];   // /www/htdocs/inc
        $basename  = $pathinfo['basename'];  // lib.inc.php
        $extension = $pathinfo['extension']; // php
        $filename  = $pathinfo['filename'];  // lib.inc

        $image = $ruta.'/'.$basename;

        // echo "Ruta: /home/patnia/domains/patnia.com/public_html\n"; 
        echo "Ruta: {$ruta}\n"; 

        if   (!file_exists($ruta)) {

            echo __LINE__." No existe: {$ruta}\n"; 

        } else if(!file_exists($image)) {

            echo __LINE__." No existe: {$image}\n"; 

        }else if(!file_exists($ruta.'/ascii_'.$basename)) {
            
            echo $image.' --> '.$ruta.'/ascii_'.$basename.' ..';
            
            $op = copy( $image,$ruta.'/ascii_'.$basename);
                
            if($op){
                
                echo "OK\n"; 
            
            } else {
                // echo "ERROR\n";
                $errors= error_get_last();
                echo "COPY ERROR: ".$errors['type'];
                echo "<br />\n".$errors['message'];      
            }

        }

        $imageinfo = getimagesize($ruta.'/ascii_'.$basename);

        $width  = $imageinfo[0];
        $height = $imageinfo[1];
        if($width>64 || $height >64){
            if(smart_resize_image( $ruta.'/ascii_'.$basename, 64 /*w*/, 64 /*h*/, true,  'file', false,  false )){
                $imageinfo = getimagesize($ruta.'/ascii_'.$basename);
                $width  = $imageinfo[0];
                $height = $imageinfo[1];
            }
        }
        switch ( $imageinfo[2] ) {
            case IMAGETYPE_GIF:   return ImageCreateFromGif($ruta.'/ascii_'.$basename);  break;
            case IMAGETYPE_JPEG:  return ImageCreateFromJpeg($ruta.'/ascii_'.$basename); break;
            case IMAGETYPE_PNG:   return ImageCreateFromPng($ruta.'/ascii_'.$basename);  break;
            default:              return false;
        }
    }


    function image2ascii( $image ) { 
        // return value 
        $ret = ''; 
        // open the image 
        $img = imageCreateFrom($image);  //Png($image);  //Jpeg($image);  
        // get width and height 
        $width = imagesx($img);  
        $height = imagesy($img);  
        // loop for height 

        $hex='';
        $prev_hex='';

        for($h=0;$h<$height;$h++) { 
            // loop for height 
            for($w=0;$w<=$width;$w++) {               //http://php.net/manual/es/function.imagecolorat.php
                // add color 
                
                $pixelrgb = imagecolorat($img,$w, $h);
                $cols = imagecolorsforindex($img, $pixelrgb);
                $r = $cols['red'];
                $g = $cols['green'];
                $b = $cols['blue'];
                /*
                $rgb = ImageColorAt($img, $w, $h);  
                $r = ($rgb >> 16) & 0xFF;  
                $g = ($rgb >> 8) & 0xFF;  
                $b = $rgb & 0xFF; 
                */
                $prev_hex = $hex;
                // create a hex value from the rgb 
                $hex = '#'.str_pad(dechex($r), 2, '0', STR_PAD_LEFT).str_pad(dechex($g), 2, '0', STR_PAD_LEFT).str_pad(dechex($b), 2, '0', STR_PAD_LEFT); 

                // now add to the return string and we are done 
                if($w == $width) {  

                    $ret .= '<br>';  

                } else  {  
                    
                    if ($prev_hex !== $hex) {                       //rtl compression
                        if($prev_hex!=='') $ret .= '</span>';
                        $ret .= '<span style="color:'.$hex.';">';
                    }

                    $ret .= '@';                

                }  
            }  
            $ret .= '</span>'; 
        }  
        return $ret; 
    } 


    ///////////////////////////////////////////////////

function removeAllComments($html) {
    // Eliminar comentarios CSS /* */
    $html = preg_replace('/\/\*.*?\*\//s', '', $html);
    
    // Eliminar comentarios HTML <!-- -->
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    
    // Eliminar comentarios JavaScript //
    //$html = preg_replace('/\/\/.*?(\n|$)/', '', $html);
    
    return $html;
}


    function convertImagesToInlineDOM($html, $pattern = null, $maxFileSize = 50000, $allowExternal = true) {

    $html = removeAllComments($html);
//return $html;
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    
    // Usar mb_convert_encoding para asegurar UTF-8 correcto
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    // 1. Procesar etiquetas <img>
    $images = $dom->getElementsByTagName('img');
    foreach ($images as $img) {
        processImageElement($img, $pattern, $maxFileSize, $allowExternal);
    }
    
    // 2. Procesar estilos inline con background-image
    $xpath = new DOMXPath($dom);
    $elementsWithStyle = $xpath->query('//*[@style]');
    
    foreach ($elementsWithStyle as $element) {
        processInlineStyles($element, $pattern, $maxFileSize, $allowExternal);
    }
    
    $html = $dom->saveHTML();
    $html = str_replace('<?xml encoding="UTF-8">', '', $html);
    
    return $html;
}

function processImageElement($img, $pattern, $maxFileSize, $allowExternal) {
    $src = $img->getAttribute('src');
    
    if (!$src) return;
    
    // Debug: mostrar qué URL se está procesando
    //DEBUG  echo "Procesando imagen: $src\n";
    
    if ($pattern && !preg_match($pattern, $src)) {
        //DEBUG   echo " - No coincide con el patrón, saltando...\n";
        return;
    }
    
    $dataUrl = convertImageToDataURL($src, $maxFileSize, $allowExternal);
    if ($dataUrl) {
        //DEBUG  echo " - Convertida exitosamente a data URL\n";
        $img->setAttribute('src', $dataUrl);
    } else {
       //DEBUG  echo " - No se pudo convertir\n";
    }
}

function processInlineStyles($element, $pattern, $maxFileSize, $allowExternal) {
    $style = $element->getAttribute('style');
    
    // Buscar URLs en el estilo
    preg_match_all('/url\s*\(\s*[\'"]?([^\)\'"]+)[\'"]?\s*\)/i', $style, $matches);
    
    $modified = false;
    foreach ($matches[1] as $url) {
        // Limpiar la URL
        $cleanUrl = trim($url, " \t\n\r\0\x0B\"'");
        
        //DEBUG echo "Procesando URL en estilo: $cleanUrl\n";
        
        if ($pattern && !preg_match($pattern, $cleanUrl)) {
            //DEBUG echo " - No coincide con el patrón, saltando...\n";
            continue;
        }
        
        $dataUrl = convertImageToDataURL($cleanUrl, $maxFileSize, $allowExternal);
        if ($dataUrl) {
            //DEBUG echo " - Convertida exitosamente a data URL\n";
            $style = str_replace($url, $dataUrl, $style);
            $modified = true;
        } else {
            //DEBUG echo " - No se pudo convertir\n";
        }
    }
    
    if ($modified) {
        $element->setAttribute('style', $style);
    }
}

function convertImageToDataURL($src, $maxFileSize, $allowExternal) {
    // Verificar si es una URL externa (http/https)
    if (preg_match('/^https?:\/\//', $src)) {
        if (!$allowExternal) {
            //DEBUG echo " - URLs externas no permitidas\n";
            return null;
        }
        return downloadExternalImage($src, $maxFileSize);
    }
    
    // Para archivos locales
    $filePath = getAbsolutePath($src);
    
    if (!file_exists($filePath)) {
        //DEBUG echo " - Archivo local no encontrado: $filePath\n";
        return null;
    }
    
    if (!is_readable($filePath)) {
        //DEBUG echo " - Archivo local no legible: $filePath\n";
        return null;
    }
    
    $fileSize = filesize($filePath);
    if ($fileSize > $maxFileSize) {
        //DEBUG echo " - Archivo demasiado grande: $fileSize bytes (límite: $maxFileSize)\n";
        return null;
    }
    
    $mimeType = getMimeType($filePath);
    if (!$mimeType) {
        //DEBUG echo " - No se pudo determinar el tipo MIME\n";
        return null;
    }
    
    $imageData = file_get_contents($filePath);
    if ($imageData === false) {
        //DEBUG echo " - Error leyendo el archivo\n";
        return null;
    }
    
    $base64Data = base64_encode($imageData);
    return "data:$mimeType;base64,$base64Data";
}

function downloadExternalImage($url, $maxFileSize) {
    //DEBUG echo " - Descargando imagen externa: $url\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Image Downloader)',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FAILONERROR => true
    ]);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        //DEBUG echo " - Error cURL: $error\n";
        return null;
    }
    
    if ($httpCode !== 200) {
        //DEBUG echo " - HTTP Error: $httpCode\n";
        return null;
    }
    
    if (strlen($imageData) > $maxFileSize) {
        //DEBUG echo " - Imagen demasiado grande: " . strlen($imageData) . " bytes\n";
        return null;
    }
    
    if (empty($imageData)) {
        //DEBUG echo " - Datos de imagen vacíos\n";
        return null;
    }
    
    // Determinar MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $imageData);
    finfo_close($finfo);
    
    if (!$mimeType || strpos($mimeType, 'image/') !== 0) {
        //DEBUG echo " - Tipo MIME no válido: $mimeType\n";
        return null;
    }
    
    $base64Data = base64_encode($imageData);
    //DEBUG echo " - Descarga exitosa, MIME: $mimeType, Tamaño: " . strlen($imageData) . " bytes\n";
    return "data:$mimeType;base64,$base64Data";
}

function getAbsolutePath($src) {
    // Remover query strings y fragments
    $src = preg_replace('/[#?].*$/', '', $src);
    
    // Si es una ruta absoluta del sistema
    if (strpos($src, '/') === 0) {
        return $_SERVER['DOCUMENT_ROOT'] . $src;
    }
    
    // Si es una ruta relativa
    $basePath = isset($_SERVER['DOCUMENT_ROOT']) ? 
        $_SERVER['DOCUMENT_ROOT'] : 
        dirname(__FILE__);
    
    return rtrim($basePath, '/') . '/' . ltrim($src, '/');
}

function getMimeType($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon'
    ];
    
    if (function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        if ($mime && $mime !== 'application/octet-stream') {
            return $mime;
        }
    }
    
    return $mimeTypes[$extension] ?? 'image/jpeg';
}

/*

function convertImagesToInlineSimple($html, $maxFileSize = 1000000) {
    // Primero eliminar todos los comentarios
    $html = removeAllComments($html);
    
    // Buscar y convertir etiquetas img
    preg_match_all('/<img[^>]+src=("|\')([^"\']+)\1[^>]*>/i', $html, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $fullTag = $match[0];
        $url = $match[2];
        
        echo "Procesando imagen: $url\n";
        
        $dataUrl = downloadImageToDataURL($url, $maxFileSize);
        if ($dataUrl) {
            $newTag = str_replace($url, $dataUrl, $fullTag);
            $html = str_replace($fullTag, $newTag, $html);
            echo "✓ Convertida: $url\n";
        } else {
            echo "✗ Error convirtiendo: $url\n";
        }
    }
    
    // Buscar y convertir URLs en estilos
    preg_match_all('/url\(\s*["\']?([^"\'\)]+)["\']?\s*\)/i', $html, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $fullMatch = $match[0];
        $url = $match[1];
        
        echo "Procesando URL en estilo: $url\n";
        
        $dataUrl = downloadImageToDataURL($url, $maxFileSize);
        if ($dataUrl) {
            $newStyle = str_replace($url, $dataUrl, $fullMatch);
            $html = str_replace($fullMatch, $newStyle, $html);
            echo "✓ Convertida: $url\n";
        } else {
            echo "✗ Error convirtiendo: $url\n";
        }
    }
    
    return $html;
}

function downloadImageToDataURL($url, $maxFileSize) {
    echo "Descargando: $url\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo " - Error HTTP: $httpCode\n";
        return null;
    }
    
    if (strlen($imageData) > $maxFileSize) {
        echo " - Imagen demasiado grande: " . strlen($imageData) . " bytes\n";
        return null;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $imageData);
    finfo_close($finfo);
    
    if (strpos($mimeType, 'image/') !== 0) {
        echo " - No es una imagen válida: $mimeType\n";
        return null;
    }
    
    $base64 = base64_encode($imageData);
    echo " - Descarga exitosa: $mimeType, " . strlen($imageData) . " bytes\n";
    return "data:$mimeType;base64,$base64";
}

*/