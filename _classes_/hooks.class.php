<?php

// core/hook.php

class Hook
{
    private static $actions = [];
    private static $filters = [];

    // Stack de llamadas para prevención de recursividad
    private static $call_stack = [];

    // ================================
    // Registrar Acción
    // ================================
    public static function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        self::$actions[$hook_name][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args
        ];
    }

    // ================================
    // Ejecutar Acción (con Prevención de Recursividad)
    // ================================
    public static function do_action($hook_name, ...$args)
    {
        // Verificar si el hook ya está en el stack (prevención de recursividad)
        if (in_array($hook_name, self::$call_stack)) {
            // Salir para evitar bucle infinito
            mierror::e("⚠️ Prevención de Recursividad: Hook '$hook_name' detectado en el stack.");
            return;
        }

        // Añadir al stack
        self::$call_stack[] = $hook_name;

        if (isset(self::$actions[$hook_name])) {
            // Ordenar por prioridad
            ksort(self::$actions[$hook_name]);
            foreach (self::$actions[$hook_name] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    call_user_func_array($callback['callback'], array_slice($args, 0, $callback['accepted_args']));
                }
            }
        }

        // Eliminar del stack al finalizar
        array_pop(self::$call_stack);
    }

    // ================================
    // Registrar Filtro
    // ================================
    public static function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        self::$filters[$hook_name][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args
        ];
    }

    // ================================
    // Aplicar Filtro (con Prevención de Recursividad)
    // ================================
    public static function apply_filters($hook_name, $value, ...$args)
    {
        // Verificar si el hook ya está en el stack (prevención de recursividad)
        if (in_array($hook_name, self::$call_stack)) {
            mierror::e("⚠️ Prevención de Recursividad: Filtro '$hook_name' detectado en el stack.");
            return $value;
        }

        // Añadir al stack
        self::$call_stack[] = $hook_name;

        if (isset(self::$filters[$hook_name])) {
            // Ordenar por prioridad
            ksort(self::$filters[$hook_name]);
            foreach (self::$filters[$hook_name] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $value = call_user_func_array($callback['callback'], array_merge([$value], array_slice($args, 0, $callback['accepted_args'])));
                }
            }
        }

        // Eliminar del stack al finalizar
        array_pop(self::$call_stack);

        return $value;
    }

    // ================================
    // Obtener Hooks Registrados (Para Debug)
    // ================================
    public static function get_registered_hooks()
    {
        return [
            'actions' => self::$actions,
            'filters' => self::$filters
        ];
    }
}



/*

Hook::do_action('init');
Hook::do_action('after_header');

Hook::add_action('after_header', function () {
    echo "<!-- Después del Header -->";
});

// filters

// Funcion por ejemplo que me da el titulo de la pagina
function get_title()
{
    $title = miapp()->title;
    return Hook::apply_filters('the_title', $title);
}

// y puedo hacer esto en un plugin
// Filtro para modificar el título
Hook::add_filter('the_title', function ($title) {
    return $title . ' - Modificado por Hook';
});


*/