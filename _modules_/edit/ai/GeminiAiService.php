<?php

namespace AiServices;

class GeminiAiService implements AiServiceInterface {

    private $endPoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    function askQuestion($query) {
        $api_key = GEMINI_API_KEY;
        $url = $this->endPoint . '?key=' . $api_key;

        $data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $query)
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'maxOutputTokens' => 8192
            )
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($response === false) {
            return "Error: cURL failed - " . $curlError;
        }

        $response_json = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $response_json['error']['message'] ?? 'Unknown error';
            return "Error ($httpCode): " . $errorMsg;
        }

        if (isset($response_json['candidates'][0]['content']['parts'][0]['text'])) {
            return $response_json['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Error: Unexpected response format - " . substr($response, 0, 200);
    }

    /**
     * Lee un archivo (PDF o imagen escaneada) y extrae los campos indicados.
     * @param string $filename  Ruta absoluta al archivo
     * @param array  $result    Array asociativo cuyas keys son los campos a extraer
     * @return array            El mismo array con los valores rellenados
     */
    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        if (!file_exists($filename)) {
            return $result;
        }

        $api_key  = GEMINI_API_KEY;
        $mimeType = mime_content_type($filename);
        $fileData = base64_encode(file_get_contents($filename));

        $keys     = array_keys($result);
        $keysJson = json_encode($keys, JSON_UNESCAPED_UNICODE);
        /*
        $prompt   = "Analiza el documento adjunto y extrae los siguientes campos. "
                  . "Responde ÚNICAMENTE con un objeto JSON válido con exactamente estas claves: $keysJson. "
                  . "Si un campo no se encuentra en el documento, usa string vacío \"\". "
                  . "No incluyas explicaciones, bloques de código ni texto adicional. Solo el JSON.";        
                  */
        $prompt   = "Analiza el documento adjunto y extrae los siguientes campos. "
                  . "Responde ÚNICAMENTE con un objeto JSON válido con exactamente estas claves: $keysJson. "
                  . "Si un campo no se encuentra en el documento, usa string vacío \"\". "
                  . "En el campo text del JSON pones lo que se te ocurra sobre el documento. Responde en castellano. ."
                  . "Si es un documento escaneado, intenta interpretar su contenido lo mejor posible y extrae la información "
                  . " identificando y estrayendo, datos, como dnis, nº de certiicados, NHC, nombres, direcciones, medicos, "
                  . " prioridades, edades,tipos, actas, solicitides, fechas, etc. Todos esos datos los pones en el texto del JSON. ";
        $url  = $this->endPoint . '?key=' . $api_key;
        $data = [
            'contents'         => [[
                'parts' => [
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => $fileData]],
                    ['text' => $prompt]
                ]
            ]],
            'generationConfig' => [
                'temperature'      => 0.1,
                'maxOutputTokens'  => 4096,
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return $result;
        }

        $responseJson = json_decode($response, true);
        $text         = $responseJson['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if ($text) {
            $parsed = json_decode($text, true);
            if ($parsed === null && preg_match('/\{.*\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
            }
            if ($parsed) {
                foreach ($result as $key => $_) {
                    if (isset($parsed[$key])) {
                        $result[$key] = $parsed[$key];
                    }
                }
            }
        }

        return $result;
    }

}
