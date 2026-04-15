<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run from CLI only');
}
/* TESTING CLI SERVER
echo 'hola desde el output server'.PHP_EOL;
echo 'ahora vamos a cargar '.SCRIPT_DIR_MODULE.'/index.php'.PHP_EOL;
echo 'con estos argumentos:'.PHP_EOL;
print_r($_ARGS);
*/


require SCRIPT_DIR_MODULE.'/index.php';
