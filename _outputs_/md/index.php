<?php

    
   include(SCRIPT_DIR_MODULE.'/index.php');



    // $md_file = SCRIPT_DIR_MODULES . '/noxtr/NIP-NOSTRESCROW.md';


    $markdown = $markdown ?? '# Markdown example';

    //echo $markdown;

    $parser = new MarkdownParser();

    // Generar la página HTML completa
    echo "<!DOCTYPE html>\n";
    echo "<html lang='es'>\n";
    echo "<head>\n";
    echo "<meta charset='UTF-8'>\n";
    echo "<title>Documentación del Protocolo de Chat</title>\n";
    echo $parser->addCSS();
    echo "</head>\n";
    echo "<body class='markdown-body'>\n";
    echo "<div class='inner'>\n";
    echo $parser->parse($markdown);
    echo "</div>\n";
    echo "</body>\n";
    echo "</html>\n";

