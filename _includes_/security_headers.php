<?php

    // Preflight CORS — responder antes de procesar nada más
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        http_response_code(204);
        exit;
    }

    $_repo = $cfg['repo']['url']??'';           
    $_cdn  = $cfg['options']['cdn_url'] ?? '';          
                                                                                  // EXAMPLES
    $_csp_frame_src       = $cfg['options']['csp_headers']['frame_src']      ??'';  // player.vimeo.com
    $_csp_script_src      = $cfg['options']['csp_headers']['script_src']     ??'';  // pixel.mathtag.com connect.facebook.net facebook.com  amazon.com aax-eu.amazon-adsystem.com api.pinterest.com s2.adform.net github.com
    $_csp_connect_src     = $cfg['options']['csp_headers']['connect_src']    ??'';  // graph.facebook.com
    $_csp_form_action_src = $cfg['options']['csp_headers']['form_action_src']??'';  //
    $_csp_style_src       = $cfg['options']['csp_headers']['style_src']      ??'';  //
    $_csp_img_src         = $cfg['options']['csp_headers']['img_src']        ??'';  //
    $_csp_font_src        = $cfg['options']['csp_headers']['font_src']       ??'';  //
    $_csp_media_src       = $cfg['options']['csp_headers']['media_src']      ??'';  // 'https://video.nostr.build
    $_csp_default_src     = $cfg['options']['csp_headers']['default_src']    ??'';  // 'https://video.nostr.build

    $csp_headers = [
        "default-src 'self' {$_csp_default_src} ", //video.nostr.build blossom.primal.net files.catbox.moe r2a.primal.net  videos.pexels.com void.cat",
        "media-src 'self' {$_csp_media_src} ", //video.nostr.build blossom.primal.net files.catbox.moe r2a.primal.net  videos.pexels.com void.cat",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.skypack.dev cdn.extralab.net bundle.run github.com track.adform.net unpkg.com gstatic.com google.com www.gstatic.com www.google.com www.google-analytics.com ajax.googleapis.com cdn.jsdelivr.net www.googletagmanager.com cdnjs.cloudflare.com {$_csp_script_src}",
        "connect-src 'self' data: www.google-analytics.com sis.redsys.es www.paypal.com {$_repo} {$_cdn} {$_csp_connect_src}", 
        "img-src 'self' blob: data: track.adform.net *",
        "style-src 'self' blob: 'unsafe-inline' cdn.extralab.net cdnjs.cloudflare.com fonts.googleapis.com cdn.jsdelivr.net {$_csp_style_src}",
        "base-uri 'self'",
        "form-action 'self' sis.redsys.es www.paypal.com {$_csp_form_action_src}",
        "font-src 'self' 'unsafe-inline' data: cdn.extralab.net fonts.gstatic.com fonts.googleapis.com cdn.jsdelivr.net cdnjs.cloudflare.com {$_csp_font_src}",
        "frame-src 'self' blob: taifpir.substack.com track.adform.net maps.google.com unpkg.com accounts.google.com www.google.com www.youtube.com jux.extralab.net/pad/ {$_csp_frame_src}",
        "worker-src 'self' blob:",
        "object-src 'self'",
        "frame-ancestors 'self'"
    ];
    
    $access_control = strpos($_SERVER['REQUEST_URI'],'sse') || strpos($_SERVER['REQUEST_URI'],'marketplace') || strpos($_SERVER['REQUEST_URI'],'shop') || strpos($_SERVER['REQUEST_URI'],'pads') // || strpos($_SERVER['REQUEST_URI'],'sse') 
                    ? '*'
                    : 'sameorigin'; // Permitir acceso a marketplace desde cualquier origen. Y a shop, para que funcionen los webhooks de la tienda online
    
    // Según Claude deberia ser '*' para SSE también, pero con 'sameorigin' funciona sin problemas y es más 
    // seguro (además de compatible con withCredentials), así que lo dejamos así por ahora. Si en el futuro
    // surge algún caso de uso concreto que requiera acceso cross-origin a SSE, se puede revisar esta decisión
    // y/o implementar una lógica más específica para SSE (ej: permitir solo los orígenes registrados en SSE_DOMAINS)
    /*
    $access_control = strpos($_SERVER['REQUEST_URI'],'sse/ajax') || strpos($_SERVER['REQUEST_URI'],'marketplace') || strpos($_SERVER['REQUEST_URI'],'shop') || strpos($_SERVER['REQUEST_URI'],'pads')
                    ? '*'
                    : (strpos($_SERVER['REQUEST_URI'],'sse')
                        ? ($_SERVER['HTTP_ORIGIN'] ?? 'sameorigin')  // SSE necesita origin específico, no * (incompatible con withCredentials)
                        : 'sameorigin');

    */



    $security_headers = [
        'Cache-Control' => 'private, must-revalidate', // para que no se cachee el contenido
      //'Cache-Control' => 'public, max-age=3600',     // para que se cachee el contenido
        'X-Frame-Options' => 'sameorigin',
        'Access-Control-Allow-Origin' => $access_control,
        'X-Content-Type-Options' => 'nosniff',
        'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        'Content-Security-Policy' => implode('; ', $csp_headers),
        'X-XSS-Protection' => '1; mode=block'
    ];

    foreach ($security_headers as $key => $value) header("$key: $value");
