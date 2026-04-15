<?php
declare(strict_types=1);

/**
 * Router Class
 *
 * Parses the request URI to determine the module and arguments.
 * It handles clean URLs and default modules for slugs.
 */
class Router
{
    /**
     * @var array The array of URL arguments.
     */
    private array $args = [];

    /**
     * @var string The determined module to load.
     */
    private string $module = '';

    /**
     * @var string The default module to use for slugs.
     */
    private string $defaultModule;

    public function __construct()
    {
        // Assuming Config class is available to get the default module
        $this->defaultModule = Config::get('ROUTER_DEFAULT_MODULE', 'page');
    }

    /**
     * Parses the REQUEST_URI to determine the module and arguments.
     *
     * @return void
     */
    public function parseRequest(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, '/');

        if (empty($path)) {
            $this->module = Config::get('ROUTER_HOME_MODULE', 'home');
            $this->args = [$this->module];
            return;
        }

        $this->args = explode('/', $path);
        $potentialModule = $this->args[0];

        if ($this->moduleExists($potentialModule)) {
            $this->module = $potentialModule;
        } else {
            // It's a slug. Prepend the default module.
            $this->module = $this->defaultModule;
            array_unshift($this->args, $this->defaultModule);
        }
    }

    /**
     * Checks if a module directory exists.
     *
     * @param string $moduleName
     * @return bool
     */
    private function moduleExists(string $moduleName): bool
    {
        // Assuming SCRIPT_DIR_MODULES is defined in configuration.php
        if (!defined('SCRIPT_DIR_MODULES')) {
            return false;
        }
        // Basic security check to prevent directory traversal
        if (strpos($moduleName, '.') !== false || strpos($moduleName, '/') !== false) {
            return false;
        }
        return is_dir(SCRIPT_DIR_MODULES . '/' . $moduleName);
    }

    /**
     * Returns the parsed URL arguments.
     *
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Returns the determined module name.
     *
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }
}
