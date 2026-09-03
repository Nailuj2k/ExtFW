<?php

/************************************************************************
 * class Template 1.4 - Class for processing templates in PHP           *
 * Copyright (C) 2000 Julio César Carrascal Urquijo.                    *
 *                    <adnoctum@eudoramail.com>                         *
 *                                                                      *
 * Modified and translated to English by: Rob Hudson                    *
 *                                                                      *
 * This library is free software; you can redistribute it and/or        *
 * modify it under the terms of the GNU Lesser General Public           *
 * License as published by the Free Software Foundation; either         *
 * version 2.1 of the License, or (at your option) any later version.   *
 *                                                                      *
 * This library is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    *
 * Lesser General Public License for more details.                      *
 *                                                                      *
 * You should have received a copy of the GNU Lesser General Public     *
 * License along with this library; if not, write to the Free Software  *
 * Foundation Inc. 59 Temple Place Suite 330 Boston, MA 02111-1307  USA *
 ************************************************************************/

/************************************************************************
 * MEJORAS Y CORRECCIONES IMPLEMENTADAS:                               *
 * ===================================================                 *
 * Fecha: Junio 2025                                                   *
 * Implementadas por: Análisis y optimización de código                *
 *                                                                     *
 * CORRECCIONES CRÍTICAS:                                              *
 * - Corregido método _extract_blocks() que causaba output no deseado  *
 *   ("page -->") al procesar templates con bloques BEGIN/END          *
 * - Inicialización correcta de variable $block_names                  *
 * - Corrección de llamada a método inexistente finish() -> _finish()  *
 * - Añadida propiedad $last_error faltante                            *
 *                                                                     *
 * OPTIMIZACIONES DE RENDIMIENTO:                                      *
 * - Sistema de cache de archivos ($file_cache) para evitar lecturas   *
 *   repetidas del disco y mejorar significativamente el rendimiento   *
 * - Reemplazo de fopen/fread/fclose por file_get_contents() más       *
 *   eficiente en _load_file()                                         *
 * - Mejora de patrones regex en _finish() para mayor precisión        *
 * - Verificación de existencia de archivos antes de leer              *
 *                                                                     *
 * MEJORAS DE SEGURIDAD:                                               *
 * - Parámetro opcional $escape_html en set_var() para prevenir XSS    *
 * - Método set_var_safe() como atajo para asignación segura           *
 * - Escape HTML con ENT_QUOTES y codificación UTF-8                   *
 *                                                                     *
 * NUEVAS FUNCIONALIDADES:                                             *
 * - clear_vars(): Limpiar variables para reutilizar la instancia      *
 * - clear_blocks(): Limpiar todos los bloques del template            *
 * - block_exists(): Verificar existencia de bloques                   *
 * - var_exists(): Verificar existencia de variables                   *
 *                                                                     *
 * COMPATIBILIDAD:                                                     *
 * - Todas las mejoras mantienen 100% compatibilidad con código        *
 *   existente, solo añaden funcionalidades opcionales                 *
 ************************************************************************/

if(defined("CLASS_TEMPLATE_PHP")) return;
define("CLASS_TEMPLATE_PHP", 1);

/***************************************************************
 * class Template.                                             *
 ***************************************************************/

class Template {
    var $classname = "Template";
    var $root = ".";
    var $unknowns = "remove";       // "remove" | "comment" | "keep"
    var $halt_on_error = "yes";     // "yes" | "report" | "no"
    var $auto_scan_globals = true;  // "true" | "false"
    var $DEBUG = false;             // "true" | "false"

    /*************************************************************
     * Format of the date, time and datetime variables.          *
     *************************************************************/
    var $locale_string = "es_ES";
    var $datetime_format = "%A %d de %B %Y - %I:%M:%S %p";
    var $date_format = "%A %d de %B %Y";
    var $time_format = "%I:%M:%S %p";

    var $blocks = array();
    var $vars = array();
    var $last_error = "";
    var $file_cache = array(); // Cache de archivos leídos

    /*************************************************************
     * Template([string $root], [string $unknowns]);             *
     * Constructor. $root is the directory were all templates    *
     * will be searched for. $unknows especify what to do with   *
     * undefined variables.                                      *
     *************************************************************/
    function Template($root = ".", $unknowns = "") {
        if($this->DEBUG) { print("<li><b>Constructor</b></li>\n".$this->_show_args(func_get_args())); }
        $this->set_root($root);
        if($unknowns)
            $this->set_unknowns($unknowns);
//        if($this->auto_scan_globals)
//            $this->scan_globals();
    }

    /*************************************************************
     * void set_root(string $root);                              *
     * $root is the directory were all templates will be         *
     * searched for.                                             *
     *************************************************************/
    function set_root($root) {
        if($this->DEBUG) { print("<li><b>set_root</b></li>\n".$this->_show_args(func_get_args())); }
        if(!is_dir($root)) {
            $this->_halt("set_root: $root isn't a directory.");
            return false;
        }
        $this->root = $root;
        return true;
    }

    /*************************************************************
     * void set_file(mixed $name, [string $filename]);           *
     * Read $filename and store in block $name. If $name is an   *
     * array, create the array like this:                        *
     *     array("name" => "filename", ...);                     *
     *                                                           *
     *************************************************************/
    function set_file($filename, $name = "out") {
        if($this->DEBUG) { print("<li><b>set_file</b></li>\n".$this->_show_args(func_get_args())); }
        if(is_array($filename)) {
            foreach($filename as $k => $v) {
                $this->_extract_blocks($k, $this->_load_file($v));
            }
        } else {
            $this->_extract_blocks($name, $this->_load_file($filename));
        }
    }

    /*************************************************************
     * void set_var(mixed $var, [string $value]);                *
     *                                                           *
     * Assign $value to $var. If $var is an array of the form    *
     * array("var"=>"value") then assign each value in the array.*
     * Now with better XSS protection and encoding handling.     *
     *************************************************************/
    function set_var($var, $value = "", $escape_html = false) {
        if($this->DEBUG) { print("<li><b>set_var</b></li>\n".$this->_show_args(func_get_args())); }
        if(is_array($var)) {
            foreach($var as $k => $v) {
                $processed_value = $escape_html ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : $v;
                $this->vars["/\{$k}/"] = $processed_value;
            }
        } else {
            $processed_value = $escape_html ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
            $this->vars["/\{$var}/"] = $processed_value;
        }
    }

    /*************************************************************
     * string parse(string $target, [string $block], [bool $append]);
     *                                                           *
     * Process the block specified by $block and store the       *
     * result in $target. If $block is not specified assume it   *
     * is the same as $target. $append specifies if we should    *
     * append (default) or not append the result of the parsed   *
     * block to $target.                                         *
     *************************************************************/
    function parse($target, $block = "", $append = true) {
        if($this->DEBUG) { print("<li><b>parse</b></li>\n".$this->_show_args(func_get_args())); }
        if($block == "") {
            $block = $target;
        }
        if(isset($this->blocks["/\{$block}/"])) {
            if($append) {
                $this->vars["/\{$target}/"] .= @preg_replace(array_keys($this->vars), array_values($this->vars), $this->blocks["/\{$block}/"]);
            } else {
                $this->vars["/\{$target}/"] = @preg_replace(array_keys($this->vars), array_values($this->vars), $this->blocks["/\{$block}/"]);
            }
        } else {
            $this->_halt("parse: \"$block\" does not exist.");
        }
        return $this->vars["/\{$target}/"];
    }

    /*************************************************************
     * int pparse(string $target, [string $block], [bool $append]);
     * Process and print the specified $block. See 'parse' for a *
     * description of the arguments.                             *
     *************************************************************/
    function pparse($target = "out", $block = "", $append = 1) {
        if($this->DEBUG) { print("<li><b>pparse</b></li>\n".$this->_show_args(func_get_args())); }
        $this->parse($target, $block, $append);
        $this->_finish($target);
        //return print($this->vars["/\{$target}/"]);
        return print(str_replace(array("\\<","	\\","\n\\","\\\n",' \\','\\\\'),array('<','','','','',''),$this->vars["/\{$target}/"]));
    }

    /*************************************************************
     * int p(string $block);                                     *
     * Print the contents of $block.                             *
     *************************************************************/
    function p($block) {
        if($this->DEBUG) { print("<li><b>p</b></li>\n".$this->_show_args(func_get_args())); }
        $this->_finish($block);
       return print($this->vars["/\{$block}/"]);
  //      return print(str_replace(array(' \\','\\\\'),array('',''),$this->vars["/\{$block}/"]));
    }

    /*************************************************************
     * string o(string $block);                                  *
     * Return the contents of $block.                            *
     *************************************************************/
    function o($block) {
        if($this->DEBUG) { print("<li><b>o</b></li>\n".$this->_show_args(func_get_args())); }
        $this->_finish($block);
        return $this->vars["/\{$block}/"];
    }

    /*************************************************************
     * array get_vars(void);                                     *
     * Return an array with the defined variables.               *
     *************************************************************/
    function get_vars() {
        if($this->DEBUG) { print("<li><b>get_vars</b></li>\n".$this->_show_args(func_get_args())); }
        $vars = [];
        foreach($this->vars as $k => $v) {
            preg_match('/^{(.+)}$/', $k, $regs);
            if(isset($regs[1])) {
                $vars[$regs[1]] = $v;
            }
        }
        return $vars;
    }

    /*************************************************************
     * mixed get_var(mixed $varname);                           *
     * Return the contents of the variable $varname. If $varname *
     * is an array, return an array with their values.           *
     *************************************************************/
    function get_var($varname) {
        if($this->DEBUG) { print("<li><b>get_var</b></li>\n".$this->_show_args(func_get_args())); }
        if(is_array($varname)) {
            $result = [];
            foreach($varname as $k) {
                $result[$k] = $this->vars["/\{$k}/"];
            }
            return $result;
        } else {
            return $this->vars["/\{$varname}/"];
        }
    }

    /*************************************************************
     * string get(string $varname);                              *
     * Return the contents of $varname.                          *
     *************************************************************/
    function get($varname) {
        if($this->DEBUG) { print("<li><b>get</b></li>\n".$this->_show_args(func_get_args())); }
        return $this->vars["/\{$varname}/"];
    }

    /*************************************************************
     * void set_unknowns(enum $unknowns);                        *
     * Specify what to do with the undefined variables.          *
     * Options are: "remove", "comment", "keep"                  *
     *************************************************************/
    function set_unknowns($unknowns = "quiet") {
        if($this->DEBUG) { print("<li><b>set_unknowns</b></li>\n".$this->_show_args(func_get_args())); }
        $this->unknowns = $unknowns;
    }

    /*************************************************************
     * void clear_vars();                                        *
     * Clear all template variables. Useful for reusing the      *
     * template instance with different data.                    *
     *************************************************************/
    function clear_vars() {
        if($this->DEBUG) { print("<li><b>clear_vars</b></li>\n"); }
        $this->vars = array();
    }

    /*************************************************************
     * void clear_blocks();                                      *
     * Clear all template blocks. Useful for completely          *
     * resetting the template.                                   *
     *************************************************************/
    function clear_blocks() {
        if($this->DEBUG) { print("<li><b>clear_blocks</b></li>\n"); }
        $this->blocks = array();
    }

    /*************************************************************
     * bool block_exists(string $block_name);                    *
     * Check if a block exists in the template.                  *
     *************************************************************/
    function block_exists($block_name) {
        if($this->DEBUG) { print("<li><b>block_exists</b></li>\n".$this->_show_args(func_get_args())); }
        return isset($this->blocks["/\{$block_name}/"]);
    }

    /*************************************************************
     * bool var_exists(string $var_name);                        *
     * Check if a variable exists in the template.               *
     *************************************************************/
    function var_exists($var_name) {
        if($this->DEBUG) { print("<li><b>var_exists</b></li>\n".$this->_show_args(func_get_args())); }
        return isset($this->vars["/\{$var_name}/"]);
    }

    /*************************************************************
     * void set_var_safe(string $var, string $value);            *
     * Shorthand for set_var with HTML escaping enabled.         *
     *************************************************************/
    function set_var_safe($var, $value = "") {
        $this->set_var($var, $value, true);
    }

/*************************************************************
 * Private Class Methods.                                    *
 *************************************************************/

    /*************************************************************
     * void _finish(string $block);                              *
     * Process undefined variables according to unknowns setting.*
     * Optimized with better regex patterns.                     *
     *************************************************************/
    function _finish($block) {
        if($this->DEBUG) { print("<li><b>_finish</b></li>\n".$this->_show_args(func_get_args())); }
        
        if (!isset($this->vars["/\{$block}/"])) {
            return;
        }
        
        switch($this->unknowns) {
            case "keep":
                // No hacer nada, mantener variables sin procesar
                break;

            case "comment":
                // Reemplazar variables no definidas con comentarios HTML
                $this->vars["/\{$block}/"] = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', "<!-- UNDEF: \\1 -->", $this->vars["/\{$block}/"]);
                break;

            case "remove":
            default:
                // Remover todas las variables no definidas (más eficiente)
                $this->vars["/\{$block}/"] = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', "", $this->vars["/\{$block}/"]);
                break;
        }
    }

    /*************************************************************
     * string _load_file(string $filename);                      *
     * Return the contents of the file specified "$filename".    *
     * Now with file caching for better performance.             *
     *************************************************************/
    function _load_file($filename) {
        if($this->DEBUG) { print("<li><b>_load_file</b></li>\n".$this->_show_args(func_get_args())); }
        
        $file_path = $this->root."/$filename";
        
        // Verificar si el archivo ya está en caché
        if (isset($this->file_cache[$file_path])) {
            return $this->file_cache[$file_path];
        }
        
        // Verificar que el archivo existe antes de intentar abrirlo
        if (!file_exists($file_path)) {
            $this->_halt("_load_file: File $filename does not exist.");
            return "";
        }
        
        // Usar file_get_contents que es más eficiente que fopen/fread/fclose
        $file_content = file_get_contents($file_path);
        if ($file_content === false) {
            $this->_halt("_load_file: Can not read file $filename.");
            return "";
        }
        
        // Guardar en caché para futuras lecturas
        $this->file_cache[$file_path] = $file_content;
        
        return $file_content;
    }

    /*************************************************************
     * void _extract_blocks(string $name, string $block);        *
     * Extract the blocks of $block and store in $name           *
     *************************************************************/
    function _extract_blocks($name, $block) {
        if($this->DEBUG) { print("<li><b>_extract_blocks</b></li>\n".$this->_show_args(func_get_args())); }
        $level = 0;
        $current_block = $name;
        $block_names = array(); // Inicializar el array
        $blocks = explode("<!-- ", $block);
        
        // Obtener el primer bloque (el contenido antes del primer comentario)
        if(count($blocks) > 0) {
            $this->blocks["/\{$current_block}/"] .= $blocks[0];
            
            // Procesar los bloques restantes (desde el índice 1, no 2)
            for($i = 1; $i < count($blocks); $i++) {
                $block_content = $blocks[$i];
                preg_match('/^(FILE|BEGIN|END) (.+) -->(.*)$/s', $block_content, $regs);
                
                if(isset($regs[1])) {
                    switch($regs[1]) {
                        case "FILE":
                        $this->_extract_blocks($current_block, $this->_load_file($regs[2]));
                        $this->blocks["/\{$current_block}/"] .= $regs[3];
                        break;

                        case "BEGIN":
                        $this->blocks["/\{$current_block}/"] .= "\{$regs[2]}";
                        $block_names[$level++] = $current_block;
                        $current_block = $regs[2];
                        $this->blocks["/\{$current_block}/"] .= $regs[3];
                        break;

                        case "END":
                        $current_block = $block_names[--$level];
                        $this->blocks["/\{$current_block}/"] .= $regs[3];
                        break;
                    }
                } else {
                    // Si no coincide con el patrón, probablemente es un comentario normal
                    $this->blocks["/\{$current_block}/"] .= "<!-- $block_content";
                }
                unset($regs);
            }
        }
    }


    /*************************************************************
     * void scan_globals();                                     *
     * Scan all globals variables so they are available in our   *
     * templates as {G_name}. Ex: {G_PHPSELF}.                   *
     *************************************************************/
    function scan_globals() {
        foreach($GLOBALS as $k => $v) {
            $this->vars["/\{G_$k}/"] = $v;
        }
        // Date and time variables
        setlocale(LC_TIME, $this->locale_string);
        $this->vars["/\{G_DATETIME}/"] = strftime($this->datetime_format, time());
        $this->vars["/\{G_DATE}/"] = strftime($this->date_format, time());
        $this->vars["/\{G_TIME}/"] = strftime($this->time_format, time());
    }

    /*************************************************************
     * bool _halt(string $msg);                                  *
     * Dies if $halt_on_error is set to 'yes', and prints $msg.  *
     *************************************************************/
    function _halt($msg) {
        if($this->DEBUG) { print("<li><b>_halt</b></li>\n".$this->_show_args(func_get_args())); }
        $this->last_error = $msg;
        if ($this->halt_on_error != "no")
            $this->_haltmsg($msg);
        if ($this->halt_on_error == "yes")
            die("<b>Halted.</b>\n");
        return false;
    }

    /*************************************************************
     * void _haltmsg(string $msg);                               *
     * Prints $msg                                               *
     *************************************************************/
    function _haltmsg($msg) {
        if($this->DEBUG) { print("<li><b>_haltmsg</b></li>\n".$this->_show_args(func_get_args())); }
        print("<b>Template Error:</b> $msg<br>\n");
    }

    /*************************************************************
     * void _show_class_values()                                 *
     * Dumps the values for perusal. Good for debugging.         *
     *************************************************************/
    function _show_class_values() {
        reset ($this->vars);
        print("<li><b>_show_class_values:</b></li>\n<ul>\n");
        print("  <li><b>classname</b> $this->classname</li>");
        print("  <li><b>root</b> $this->root</li>");
        print("  <li><b>blocks</b></li>".$this->_show_args($this->blocks));
        print("  <li><b>vars</b></li>".$this->_show_args($this->vars));
        print("  <li><b>unknowns</b> $this->unknowns</li>");
        print("  <li><b>halt_on_error</b> $this->halt_on_error</li>\n</ul>\n");
    }

    /*************************************************************
     * void _show_class_values()                                 *
     * Format the arguments of a function call. Good for         *
     * debugging too.                                            *
     *************************************************************/
    function _show_args($arg_array) {
        $args = "<ul>";
        foreach($arg_array as $key => $value) {
            $args .= "<li>$key: ".nl2br(htmlspecialchars($value))."</li>\n";
        }
        return $args . "</ul>\n";
    }
}
