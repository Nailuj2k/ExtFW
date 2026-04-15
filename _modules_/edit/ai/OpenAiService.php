<?php

namespace AiServices;


class OpenAiService implements AiServiceInterface
{
    private $apiUrl = "https://api.openai.com/v1/chat/completions";
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

    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        return $result; // OpenAI vision no implementado aún en este servicio
    }

}