<?php
// core/plugins.php

class Plugins
{
    private $pluginDir = SCRIPT_DIR_PLUGINS;  //defined('SCRIPT_DIR_PLUGINS') ? SCRIPT_DIR_PLUGINS : '_plugins_';
    private $loadedPlugins = [];

    // ================================
    // Cargar Todos los Plugins
    // ================================
    public function load_plugins()
    {
        if (is_dir($this->pluginDir)) {
            $plugins = scandir($this->pluginDir);
            foreach ($plugins as $plugin) {
                // ================================
                // Ignorar carpetas que empiezan por "_"
                // Ejemplos: _ejemplo-plugin, _desactivado
                // ================================
                if ($plugin[0] === '_') {
                    continue;
                }

                $config_file = $this->pluginDir . '/' . $plugin . '/config.json';
                $plugin_main = $this->pluginDir . '/' . $plugin . '/main.php';

                 
                // La variable de sesión 'plugin_<plugin_name>' sirve para que las dos llamadas a file_exists 
                // se realizen una sola vez por sesión.
                $plugin_load = Vars::getSessionVar('plugin_'.$plugin)=='loaded';

                // Verificar que existe el archivo de configuración y el archivo principal
                // sólo si la variable de sesión 'plugin_<plugin>' no está en 'loaded' 
                if(!$plugin_load) $plugin_load = file_exists($config_file) && file_exists($plugin_main);

                if ($plugin_load) {

                    Vars::setSessionVar('plugin_'.$plugin, 'loaded');                   
        
                    $config = json_decode(file_get_contents($config_file), true);

                     //echo 'DEBUG: '.$config_file.' '.$plugin_main.'  OKIS :)';
                     //print_r($config);

                    // Cargar el plugin si está activo
                    if (isset($config['active']) && $config['active']) {

                        //echo 'DEBUG: loading '.$plugin_main.'<br>';
    
                        require_once $plugin_main;

                        $this->loadedPlugins[] = [
                            'name' => $config['name'] ?? $plugin,
                            'version' => $config['version'] ?? '1.0',
                            'path' => $plugin_main
                        ];
                    }
                }
            }
        }
    }

    // ================================
    // Listar Plugins Cargados
    // ================================
    public function get_loaded_plugins()
    {
        return $this->loadedPlugins;
    }

    // ================================
    // Mostrar Plugins Cargados (Debug)
    // ================================
    public function debug_loaded_plugins()
    {
        echo "<pre>";
        print_r($this->loadedPlugins);
        echo "</pre>";
    }
}