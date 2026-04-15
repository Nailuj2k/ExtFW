<?php

// ============================================================
// DEMO: Sistema de Hooks (Hook::add_action / Hook::add_filter)
// ============================================================
// Los hooks permiten que un plugin reaccione a eventos del
// ciclo de vida sin modificar el core.
//
// Hooks disponibles (disparados desde _includes_/run.php):
//   after_auth        — sesión y usuario autenticado listos
//   plugins_loaded    — todos los plugins han sido cargados
//   acl_ready         — ACL inicializado, roles disponibles
//   after_theme_init  — theme cargado (solo en salida HTML)
//   after_module_init — módulo e init del scaffold listos
//   before_output     — antes de renderizar el theme/output
//   after_output      — después de renderizar
//
// Uso: Hook::add_action('nombre_hook', callable, $prioridad);
//      Hook::add_filter('nombre_hook', callable, $prioridad);
// ============================================================

// -- ACCIÓN: inyectar un comentario HTML antes del render ----
Hook::add_action('before_output', function () {
    // echo '<!-- Plugin example activo en módulo: ' . MODULE . ' -->' . NL;
});

// -- ACCIÓN: log de módulo visitado (solo usuarios logados) --
Hook::add_action('after_auth', function () {
    // if ($_SESSION['valid_user']) {
    //     LOG::event('visit', MODULE);
    // }
});

// -- FILTRO: modificar el título de página desde un plugin ---
// Hook::add_filter('page_title', function ($title) {
//     return $title . ' · Mi Sitio';
// });

// ============================================================
//   FIN DEMO HOOKS — descomenta lo que necesites
// ============================================================

   //echo 'Hola desde plugin example<br>';


    APP::$shortcodes->add_shortcode('year', function () {
       return date('Y');
    });



   APP::$shortcodes->add_shortcode('note', function ($atts) {

      $id = isset($atts['id']) ? $atts['id'] : '#';
      if($id='bitcoin-conversation-with-grok')
      return '<strong>Nota de queesbitcoin:</strong> Grok insiste una y otra vez en que se le puede preguntar por el documento y te confirmará su coautoría, pero no es cierto, si le preguntas no tiene ni idea, pues no guardda una memoria entre sesiones. Menudo mentiroso es. :)';
      else 
      return 'queesbitcoin.net'; //.' '.$id;

   });

/*
function calcularHashDesdeUrl($url) {
    // Obtener el contenido de la URL
    $texto = file_get_contents($url);
    
    if ($texto === false) {
        return "Error al obtener el contenido de la URL";
    }
    
    // Calcular el hash SHA-256 del texto plano
    $hash = hash('sha256', $texto);
    
    return $hash;
}

// Ejemplo de uso
$urlTexto = 'https://queesbitcoin.net/bitcoin-conversation-with-grok/text';
$hash = calcularHashDesdeUrl($urlTexto);

// Mostrar el hash en la nota (solo en la versión HTML)
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/html') !== false) {
    echo '<p><strong>Nota sobre autenticidad:</strong> Este artículo es una conversación auténtica entre Erwin Schrodinger y Grok (xAI). El hash SHA-256 del contenido principal, disponible en <a href="' . $urlTexto . '">esta URL</a>, es <code>' . $hash . '</code>. Podés verificarlo extrayendo el texto plano de esa URL y calculando su hash. También podés preguntarle a Grok proporcionándole el texto o el hash.</p>';
}

*/   