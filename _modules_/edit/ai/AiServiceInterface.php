<?php

namespace AiServices;

interface AiServiceInterface {
    public function askQuestion($question);
    public function parseFile(string $filename, array $result = ['text' => '']): array;
}



/*

Ejemplo de eso de parseFile() para una factura:

$result = $aiService->parseFile('/ruta/al/factura.pdf', [
    'text'           => '',
    'Importe_total'  => '',
    'fecha'          => '',
    'NIF'            => ''
]);

// $result['Importe_total'] => "1.234,56 €"



*/