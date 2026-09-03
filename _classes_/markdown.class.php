<?php

class MarkdownParser {
    private $html;
    private $markdown;
    private $insideCode = false;
    private $insideList = false;
    
    public function __construct($markdown = '') {
        $this->markdown = $markdown;
    }
    
    public function parse($markdown = null) {
        if ($markdown !== null) {
            $this->markdown = $markdown;
        }
        
        $this->html = '';
        $this->insideCode = false;
        $this->insideList = false;
        
        // Dividir en líneas
        $lines = explode("\n", $this->markdown);
        
        // Procesar cada línea
        foreach ($lines as $line) {
            $this->parseLine($line);
        }
        
        // Cerrar cualquier lista abierta
        if ($this->insideList) {
            $this->html .= "</ul>\n";
        }
        
        return $this->html;
    }
    
    private function parseLine($line) {
        $line = trim($line);
        
        // Ignorar líneas vacías excepto dentro de bloques de código
        if (empty($line) && !$this->insideCode) {
            $this->html .= "<br>\n";
            if ($this->insideList) {
                $this->html .= "</ul>\n";
                $this->insideList = false;
            }
            return;
        }
        
        // Bloque de código
        if (preg_match('/^```(.*)$/', $line, $matches)) {
            if ($this->insideCode) {
                $this->html .= "</code></pre>\n";
                $this->insideCode = false;
            } else {
                $language = $matches[1];
                $this->html .= "<pre><code" . ($language ? " class=\"language-$language\"" : "") . ">";
                $this->insideCode = true;
            }
            return;
        }
        
        // Si estamos dentro de un bloque de código
        if ($this->insideCode) {
            $this->html .= htmlspecialchars($line) . "\n";
            return;
        }
        
        // Encabezados
        if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $matches)) {
            $level = strlen($matches[1]);
            $text = $this->parseInline($matches[2]);
            $this->html .= "<h$level>$text</h$level>\n";
            return;
        }
        
        // Listas no ordenadas
        if (preg_match('/^[\-\*]\s+(.*)$/', $line, $matches)) {
            if (!$this->insideList) {
                $this->html .= "<ul>\n";
                $this->insideList = true;
            }
            $this->html .= "<li>" . $this->parseInline($matches[1]) . "</li>\n";
            return;
        }
        
        // Enlaces
        $line = preg_replace_callback('/\[([^\]]+)\]\(([^\)]+)\)/', function($matches) {
            return '<a href="' . htmlspecialchars($matches[2]) . '">' . $matches[1] . '</a>';
        }, $line);
        
        // Si no es ninguno de los anteriores, es un párrafo
        if (!$this->insideList) {
            $this->html .= "<p>" . $this->parseInline($line) . "</p>\n";
        }
    }
    
    private function parseInline($text) {
        // Negrita
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
        
        // Cursiva
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_(.+?)_/', '<em>$1</em>', $text);
        
        // Código inline
        $text = preg_replace('/`(.+?)`/', '<code>$1</code>', $text);
        
        return $text;
    }
    
    public function addCSS() {
        return <<<CSS
        <style>
            /*
            .markdown-body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
                font-size: 16px;
                line-height: 1.5;
                word-wrap: break-word;
                max-width: 800px;
                margin: 0 auto;
                padding: 2em;
            }
            .markdown-body h1, .markdown-body h2 {
                padding-bottom: .3em;
                border-bottom: 1px solid #eaecef;
            }
            .markdown-body h1 { font-size: 2em; }
            .markdown-body h2 { font-size: 1.5em; }
            .markdown-body h3 { font-size: 1.25em; }
            .markdown-body h4 { font-size: 1em; }
            */
            .markdown-body code {
                padding: .1em .2em;
                /* font-size: 85%;*/
                background-color: rgba(27,31,35,.05);
                border-radius: 3px;
                font-family: "SFMono-Regular",Consolas,"Liberation Mono",Menlo,Courier,monospace;
            }
            .markdown-body pre {
                margin: 0 20px;
                padding: 16px;
                overflow: auto;
                /*font-size: 85%;*/
                /*line-height: 1.45;*/
                background-color: #f6f8fadd;
  
                border-radius: 3px;
            }
            .markdown-body pre code {
                display: block;
                padding: 0;
                margin: 0;
                overflow: visible;
                line-height: inherit;
                word-wrap: normal;
                background-color: transparent;
                border: 0;
            }/*
            .markdown-body ul {
                padding-left: 2em;
            }*/
            .markdown-body img {
                max-width: 100%;
            }

            /*
            .markdown-body blockquote {
                padding: 0 1em;
                color: #6a737d;
                border-left: .25em solid #dfe2e5;
                margin: 0;
            }
            .markdown-body table {
                border-spacing: 0;
                border-collapse: collapse;
            }
            .markdown-body table th,
            .markdown-body table td {
                padding: 6px 13px;
                border: 1px solid #dfe2e5;
            }
            .markdown-body table tr:nth-child(2n) {
                background-color: #f6f8fa;
            }
            */
        </style>
        CSS;
    }
}