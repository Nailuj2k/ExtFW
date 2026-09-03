<?php

namespace AiServices;


class OpenAiService implements AiServiceInterface
{
    private $apiUrl = "https://api.openai.com/v1/chat/completions";
    private $fileModel = 'gpt-4.1';   // modelo usado por parseFile(), debe aceptar input_file
    private $apiKey;
    private $historyFile;

    public function __construct()
    {
        $this->apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null;
        $this->historyFile = SCRIPT_DIR_MODULE . '/history.openai.' . session_id() . '.json';

        if (!$this->apiKey) {
            throw new \Exception("La clave API de OpenAI no está definida.");
        }
    }

    public function askQuestion($query)
    {
        if (empty($query)) {
            return "Error: La consulta no puede estar vacía.";
        }

        // Cargar historial de mensajes
        $history = $this->loadHistory();

        // Añadir nuevo mensaje del usuario
        $history[] = ["role" => "user", "content" => $query];

        // Configurar datos para la API
        $payload = [
            'model' => 'gpt-4',
            'messages' => $history,
            'temperature' => 0.7
        ];

        // Realizar la solicitud a OpenAI
        $response = $this->sendRequest($payload);

        // Procesar la respuesta
        if ($response && isset($response['choices'][0]['message']['content'])) {
            $result = $response['choices'][0]['message']['content'];

            // Extraer solo el código del mensaje
            $result = $this->extractCode($result);
            
            // Guardar la respuesta en el historial
            $history[] = ["role" => "assistant", "content" => $result];
            $this->saveHistory($history);

            return $result;
        }

        return "Error: No se recibió una respuesta válida de la API.";
    }

    private function loadHistory()
    {
        if (file_exists($this->historyFile)) {
            $content = file_get_contents($this->historyFile);
            return json_decode($content, true) ?? [];
        }
        return [];
    }

    private function saveHistory($history)
    {
        file_put_contents($this->historyFile, json_encode($history, JSON_UNESCAPED_UNICODE));
    }

    private function sendRequest($data)
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = 'Error en cURL: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        }

        curl_close($ch);

        // Verificar si la respuesta es válida
        if ($httpCode !== 200) {
            return ["error" => "HTTP $httpCode: " . json_decode($response, true)['error']['message'] ?? "Error desconocido"];
        }

        return json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Extrae el código contenido entre bloques markdown (```)
     * Si no se encuentra ningún bloque, devuelve el texto completo.
     */
    private function extractCode($text) {
        // Este patrón busca bloques de código delimitados por triple backticks.
        if (preg_match('/```(?:[a-zA-Z0-9]+)?\s*(.*?)\s*```/s', $text, $matches)) {
            return trim($matches[1]);
        }
        // Si no se encuentra ningún bloque de código, se devuelve el texto tal cual.
        return $text;
    }

    /**
     * Lee un archivo (PDF o imagen escaneada) y extrae los campos indicados.
     * Usa la Responses API: chat/completions no acepta input_file.
     * @param string $filename  Ruta absoluta al archivo
     * @param array  $result    Array asociativo cuyas keys son los campos a extraer
     * @return array            El mismo array con los valores rellenados
     */
    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        if (!file_exists($filename)) {
            return $result;
        }

        $mimeType = mime_content_type($filename);
        $dataUri  = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($filename));

        if ($mimeType === 'application/pdf') {
            $content = ['type' => 'input_file', 'filename' => basename($filename), 'file_data' => $dataUri];
        } else {
            $content = ['type' => 'input_image', 'image_url' => $dataUri];
        }

        $keysJson = json_encode(array_keys($result), JSON_UNESCAPED_UNICODE);
        $prompt   = "Analiza el documento adjunto y extrae los siguientes campos. "
                  . "Responde ÚNICAMENTE con un objeto JSON válido con exactamente estas claves: $keysJson. "
                  . "Si un campo no se encuentra en el documento, usa string vacío \"\". "
                  . "En el campo text del JSON pones lo que se te ocurra sobre el documento. Responde en castellano. "
                  . "Si es un documento escaneado, intenta interpretar su contenido lo mejor posible y extrae la información "
                  . "identificando y extrayendo datos como dnis, nº de certificados, NHC, nombres, direcciones, medicos, "
                  . "prioridades, edades, tipos, actas, solicitudes, fechas, etc. Todos esos datos los pones en el texto del JSON.";

        $data = [
            'model' => $this->fileModel,
            'input' => [[
                'role'    => 'user',
                'content' => [$content, ['type' => 'input_text', 'text' => $prompt]]
            ]],
            'text'  => ['format' => ['type' => 'json_object']]
        ];

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return $result;
        }

        $responseJson = json_decode($response, true);

        // output[] puede traer items previos (reasoning) antes del mensaje.
        $text = '';
        foreach ($responseJson['output'] ?? [] as $item) {
            foreach ($item['content'] ?? [] as $part) {
                if (($part['type'] ?? '') === 'output_text') {
                    $text = $part['text'];
                    break 2;
                }
            }
        }

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