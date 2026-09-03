<?php
        require SCRIPT_DIR_CLASSES . '/ico.class.php';
        require SCRIPT_DIR_CLASSES . '/favicon.class.php';

        // favicon.ico (multi-tamaño)
        $source      = SCRIPT_DIR_MEDIA.'/images/favicon.png';
        $destination = './favicon.ico';
        $sizes       = [[16,16],[24,24],[32,32],[48,48]];
        $ico_lib     = new PHP_ICO($source, $sizes);
        $ico_lib->save_ico($destination);

        // Parámetros de configuración
        $faviconDir             = 'media/icons/';
        $imageForAndroid        = CFG::$vars['app']['android-icon'] ?: 'media/images/favicon.png';
        $imageForApple          = CFG::$vars['app']['apple-icon']   ?: 'media/images/favicon.png';
        $applicationName        = CFG::$vars['app']['title']        ?: CFG::$vars['site']['title'];
        $msapplicationTileColor = CFG::$vars['app']['tile-color']   ?: '#c07b39';
        $themeColor             = CFG::$vars['app']['theme-color']  ?: '#c07b39';
        $titleBarColor          = CFG::$vars['app']['bar-color']    ?: '#c07b39';
        $appleStartupImageProportion = 80.0;

        // Generar imágenes PNG
        $faviconImageGenerator = new FaviconImageGenerator(
            $applicationName, $faviconDir,
            $imageForAndroid, $imageForApple,
            $appleStartupImageProportion
        );
        $faviconImageGenerator->generate();

        // Generar HTML e iconos
        $v = date('dmyHis');
        $faviconHtmlGenerator = new FaviconHtmlGenerator(
            $applicationName, $faviconDir,
            $msapplicationTileColor, $themeColor, $titleBarColor
        );

        // Escribir icons.php
        $fichero   = './media/icons/icons.php';
        $contenido = "\n".'        '.str_replace(' />'," />\n".'        ', $faviconHtmlGenerator->generate($v));
        if ($hfp = fopen($fichero, 'w')) { fwrite($hfp, stripslashes($contenido)); fclose($hfp); }

        // Escribir manifest.json (con campos PWA completos)
        file_put_contents('./media/icons/manifest.json', $faviconHtmlGenerator->generateManifest());

        $result['msg'] = 'Favicons actualizados';
