<?php

    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');    // Evitar MIME-type sniffing
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
   // header('X-Frame-Options: SAMEORIGIN');  //Comentado porque se confogura en security_headers.php
    header('X-XSS-Protection: 1; mode=block');

    // Iniciar captura de salida para detectar automáticamente el tipo de contenido
    ob_start();

    $app_loaded = include(SCRIPT_DIR_MODULE.'/index.php');

    // Capturar el contenido generado
    $output = ob_get_contents();
    ob_end_clean();

    // Detectar automáticamente si el contenido es JSON válido
    $is_json = false;
    if (!empty($output)) {
        // Limpiar espacios en blanco al inicio y final
        $trimmed_output = trim($output);
        
        // Verificar si parece JSON (empieza con { o [)
        if ((substr($trimmed_output, 0, 1) === '{' && substr($trimmed_output, -1) === '}') ||
            (substr($trimmed_output, 0, 1) === '[' && substr($trimmed_output, -1) === ']')) {
            
            // Verificar que es JSON válido
            json_decode($trimmed_output);
            if (json_last_error() === JSON_ERROR_NONE) {
                $is_json = true;
            }
        }
    }

    // Establecer el Content-Type apropiado automáticamente
    if ($is_json) {
        header('Content-Type: application/json');
    } else {
        header('Content-Type: text/html; charset=utf-8');
    }

    // Enviar el contenido
    echo $output;

    if(!$app_loaded) {
        if ($is_json) {
            echo json_encode(['error' => 'No existe la aplicación '.$_DIR_MODULE]);
        } else {
            echo '<b>No existe la aplicación '.$_DIR_MODULE.'</b>';
        }
        exit;
    }

    // header('Access-Control-Allow-Origin: https://tudominio.com');
    // header('Access-Control-Allow-Credentials: true');