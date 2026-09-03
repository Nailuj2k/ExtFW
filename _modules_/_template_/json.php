<?php
// Este archivo sirve como ejemplo de salida JSON para el módulo _template_

// Puedes personalizar esta estructura según lo que necesite tu módulo

$data = [
    'module' => MODULE,
    'args' => $_ARGS,
    'message' => 'Esto es una respuesta JSON de ejemplo desde el módulo _template_'
];

// Devolver el JSON con formato legible y sin escapar los caracteres Unicode
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);