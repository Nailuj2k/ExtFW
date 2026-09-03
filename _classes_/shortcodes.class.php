<?php
// core/shortcodes.php
/*********
class Shortcodes
{
    private $shortcodes = [];

    // ================================
    // Registrar un Shortcode
    // ================================
    public function add_shortcode($tag, $callback)
    {
        $this->shortcodes[$tag] = $callback;
    }

    // ================================
    // Procesar y Reemplazar Shortcodes
    // Mejorado para Eliminar Restos de Shortcodes
    // ================================
    public function do_shortcode($content, $depth = 0)
    {

        // Limitar la profundidad de recursividad para evitar bucles infinitos
        $max_depth = 10;
        if ($depth > $max_depth) {
            return $content;
        }

        // Verificar si el contenido es válido
        if (empty($content) || !is_string($content)) {
            return $content;
        }

        // Procesar Shortcodes con Cierre
        foreach ($this->shortcodes as $tag => $callback) {
            $pattern_wrap = '/\[' . $tag . '( [^\]])?\](.?)\[\/' . $tag . '\]/s';

            $content = preg_replace_callback($pattern_wrap, function ($matches) use ($callback, $depth) {
                $atts_text = isset($matches[1]) ? trim($matches[1]) : '';
                $inner_content = isset($matches[2]) ? $matches[2] : '';

                // Extraer atributos
                $atts = [];
                preg_match_all('/(\w+)=["\']([^"\']+)["\']/', $atts_text, $atts_matches, PREG_SET_ORDER);
                foreach ($atts_matches as $att) {
                    $atts[$att[1]] = $att[2];
                }

                // 🔥 Llamada Recursiva con Profundidad Controlada
                $inner_content = $this->do_shortcode($inner_content, $depth + 1);

                return call_user_func($callback, $atts, $inner_content);
            }, $content);

            // Procesar Shortcodes sin Cierre
            $pattern_single = '/\[' . $tag . '( [^\]])?\](?!.\[\/' . $tag . '\])/';
            $content = preg_replace_callback($pattern_single, function ($matches) use ($callback) {
                $atts_text = isset($matches[1]) ? trim($matches[1]) : '';

                // Extraer atributos
                $atts = [];
                preg_match_all('/(\w+)=["\']([^"\']+)["\']/', $atts_text, $atts_matches, PREG_SET_ORDER);
                foreach ($atts_matches as $att) {
                    $atts[$att[1]] = $att[2];
                }

                return call_user_func($callback, $atts, null);
            }, $content);
        }

        // 🔥 Balancear Etiquetas Abiertas y Cerradas
        $opened_tags = [];
        preg_match_all('/<([a-z]+)(?=[\s>])/i', $content, $result);
        $opened_tags = $result[1];

        $closed_tags = [];
        preg_match_all('/<\/([a-z]+)>/i', $content, $result);
        $closed_tags = $result[1];

        $opened_tags = array_reverse($opened_tags);
        foreach ($opened_tags as $tag) {
            if (!in_array($tag, $closed_tags)) {
                $content .= "</$tag>";
            } else {
                unset($closed_tags[array_search($tag, $closed_tags)]);
            }
        }

        // 🔥 Eliminar Cierres Huérfanos de Shortcodes
        $content = preg_replace('/\[\/\w+\]/', '', $content);

        return $content;
    }
}
*****/

class Shortcodes
{
    private $shortcodes = [];

    // ================================
    // Registrar un Shortcode
    // ================================
    public function add_shortcode($tag, $callback)
    {
        $this->shortcodes[$tag] = $callback;
    }

    // ================================
    // Procesar y Reemplazar Shortcodes
    // Mejorado para Eliminar Restos de Shortcodes
    // ================================
    public function do_shortcode($content, $depth = 0)
    {

        // Limitar la profundidad de recursividad para evitar bucles infinitos
        $max_depth = 10;
        if ($depth > $max_depth) {
            return $content;
        }

        // Verificar si el contenido es válido
        if (empty($content) || !is_string($content)) {
            return $content;
        }

        // Procesar Shortcodes con Cierre
        foreach ($this->shortcodes as $tag => $callback) {
            $pattern_wrap = '/\[' . $tag . '(.*?)\](.*?)\[\/' . $tag . '\]/s';

            $content = preg_replace_callback($pattern_wrap, function ($matches) use ($callback, $depth) {
                $atts_text = isset($matches[1]) ? trim($matches[1]) : '';
                $inner_content = isset($matches[2]) ? $matches[2] : '';

                // Extraer atributos (con o sin comillas)
                $atts = [];
                preg_match_all('/(\w+)=(?:["\']([^"\']*)["\']|([^\s\]]+))/', $atts_text, $atts_matches, PREG_SET_ORDER);
                foreach ($atts_matches as $att) {
                    $atts[$att[1]] = isset($att[3]) && $att[3] !== '' ? $att[3] : $att[2];
                }

                // Llamada Recursiva con Profundidad Controlada
                $inner_content = $this->do_shortcode($inner_content, $depth + 1);

                return call_user_func($callback, $atts, $inner_content);
            }, $content);

            // Procesar Shortcodes sin Cierre
            $pattern_single = '/\[' . $tag . '(.*?)\]/';
            $content = preg_replace_callback($pattern_single, function ($matches) use ($callback, $tag) {
                // Ignorar si es parte de un shortcode de cierre
                if (strpos($matches[0], '[/' . $tag . ']') !== false) {
                    return $matches[0];
                }

                $atts_text = isset($matches[1]) ? trim($matches[1]) : '';

                // Extraer atributos (con o sin comillas)
                $atts = [];
                preg_match_all('/(\w+)=(?:["\']([^"\']*)["\']|([^\s\]]+))/', $atts_text, $atts_matches, PREG_SET_ORDER);
                foreach ($atts_matches as $att) {
                    $atts[$att[1]] = isset($att[3]) && $att[3] !== '' ? $att[3] : $att[2];
                }

                return call_user_func($callback, $atts, null);
            }, $content);
        }

        // Balancear Etiquetas Abiertas y Cerradas
        $opened_tags = [];
        preg_match_all('/<([a-z]+)(?=[\s>])/i', $content, $result);
        $opened_tags = $result[1];

        $closed_tags = [];
        preg_match_all('/<\/([a-z]+)>/i', $content, $result);
        $closed_tags = $result[1];

        // Lista de etiquetas auto-cerradas en HTML
        $self_closing = ['br', 'hr', 'img', 'input', 'meta', 'link', 'base', 'area', 'col', 'embed', 'keygen', 'param', 'source', 'track', 'wbr'];

        $opened_tags = array_reverse($opened_tags);
        foreach ($opened_tags as $tag) {
            
            // Ignorar etiquetas auto-cerradas
            if (in_array(strtolower($tag), $self_closing)) {
                continue;
            }

            if (!in_array($tag, $closed_tags)) {
                $content .= "</$tag>";
            } else {
                unset($closed_tags[array_search($tag, $closed_tags)]);
            }
        }

        // Eliminar Cierres Huérfanos de Shortcodes
        $content = preg_replace('/\[\/\w+\]/', '', $content);

        return $content;
    }
}




/***




                             $html = htmlentities($_TIDY_TEXT_);
                             
                             include(SCRIPT_DIR_CLASSES.'/shortcodes.class.php');
                             $sc = new Shortcodes();

                             $sc->add_shortcode('year', function () {
                                return date('Y');
                             });

                             echo $sc->do_shortcode($html);

***/
