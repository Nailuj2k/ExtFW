<?php

    APP::$shortcodes->add_shortcode('latest_posts', function ($atts) {
        $limit = isset($atts['limit']) ? intval($atts['limit']) : 5;
        $posts = Table::sqlQuery("SELECT item_title AS title ,item_name AS url FROM CLI_PAGES LIMIT $limit");
        $output = '<ul>';
        foreach ($posts as $post) {
            $output .= '<li><a href="' . $post['url'] . '">' . $post['title'] . '</a></li>';
        }
        $output .= '</ul>';
        return $output;
    });

    APP::$shortcodes->add_shortcode('ajax', function ($atts) {
        $url = isset($atts['url']) ? $atts['url'] : 'news/html';
        $hash = hash('crc32b', $url );
        $output = '<div id="ajax-content-'.$hash.'"><p style="text-align:center">Loading ... <!--'. $url .'--></p><span class="ajaxloader"></span></div>'
                . '<script>'
                . 'setTimeout(async function(){'
                . '    let url = `/'.$url.'`;'
                . '    loadContent(`#ajax-content-'.$hash.'`,url);'
                . '},1000);'
                . '</script>';
        return $output;
    });

    APP::$shortcodes->add_shortcode('jsfiddle', function ($atts) {
        $code = isset($atts['code']) ? $atts['code'] : '#';
        $tabs = isset($atts['tabs']) ? $atts['tabs'] : '';
        $output = '<iframe width="100%" height="300" src="//jsfiddle.net/Imabot/'.$code.'/embedded/'.$tabs.'" frameborder="0" loading="lazy" allowtransparency="true" allowfullscreen="true"></iframe>';
        //$output = '<script async src="//jsfiddle.net/Nailuj2000/'.$code.'/3/embed/'.$tabs.'/"></script>';
        return $output;
    });

    APP::$shortcodes->add_shortcode('hash', function ($atts) {
        $url = isset($atts['url']) ? $atts['url'] : '#';
        $texto = file_get_contents($url);
        if ($texto === false) {
            $hash = '{no hash}';
        }else{
            $hash = hash('sha256', $texto);    // Calcular el hash SHA-256 del texto plano
        }
        return $hash;
        // return '<a href="' . $url . '" class="btn">' . $text . '</a>';

    });
