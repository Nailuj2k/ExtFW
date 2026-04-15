<?php

namespace AiServices;

/**
 * Claude AI Service (Anthropic API)
 *
 * Para usar este servicio:
 * 1. Define CLAUDE_API_KEY en tu archivo de configuración
 * 2. Obtén tu API key desde: https://console.anthropic.com/
 *
 * Ejemplo en config:
 * define('CLAUDE_API_KEY', 'sk-ant-api03-...');
 */
class ClaudeAiService implements AiServiceInterface
{
    private $apiUrl = "https://api.anthropic.com/v1/messages";
    private $apiKey;
    private $historyFile;
    private $model = "claude-sonnet-4-5-20250929"; // Claude Sonnet 4.5 - El más reciente

    public function __construct()
    {
        $this->apiKey = defined('CLAUDE_API_KEY') ? CLAUDE_API_KEY : null;
        $this->historyFile = SCRIPT_DIR_MODULE . '/history.claude.' . session_id() . '.json';
    }

    public function askQuestion($query)
    {
        if (!$this->apiKey) {
            return "Error: La clave API de Claude no está definida. Define CLAUDE_API_KEY en tu configuración.";
        }

        if (empty($query)) {
            return "Error: La consulta no puede estar vacía.";
        }

        // Cargar historial de mensajes
        $history = $this->loadHistory();

        // Añadir nuevo mensaje del usuario
        $history[] = ["role" => "user", "content" => $query];

        // Configurar datos para la API de Claude
        // Claude usa un formato diferente a OpenAI
        $payload = [
            'model' => $this->model,
            'messages' => $history,
            'max_tokens' => 4096,
            'temperature' => 0.7
        ];

        // Realizar la solicitud a Claude
        $response = $this->sendRequest($payload);

        // Procesar la respuesta
        if ($response && isset($response['content'][0]['text'])) {
            $result = $response['content'][0]['text'];

            // Extraer solo el código del mensaje si viene en bloques markdown
            $result = $this->extractCode($result);

            // Guardar la respuesta en el historial
            $history[] = ["role" => "assistant", "content" => $result];
            $this->saveHistory($history);

            return $result;
        }

        // Manejar errores de la API
        if (isset($response['error'])) {
            return "Error Claude API: " . $response['error']['message'];
        }

        return "Error: No se recibió una respuesta válida de Claude API.";
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
                "x-api-key: " . $this->apiKey,
                "anthropic-version: 2023-06-01"
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = 'Error en cURL: ' . curl_error($ch);
            curl_close($ch);
            return ["error" => ["message" => $error]];
        }

        curl_close($ch);

        // Verificar si la respuesta es válida
        if ($httpCode !== 200) {
            $errorResponse = json_decode($response, true);
            return ["error" => [
                "message" => "HTTP $httpCode: " . ($errorResponse['error']['message'] ?? $response)
            ]];
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
     * Limpia el historial de chat
     */
    public function clearHistory()
    {
        if (file_exists($this->historyFile)) {
            unlink($this->historyFile);
        }
    }

    /**
     * Lee un archivo (PDF o imagen escaneada) y extrae los campos indicados.
     * @param string $filename  Ruta absoluta al archivo
     * @param array  $result    Array asociativo cuyas keys son los campos a extraer
     * @return array            El mismo array con los valores rellenados
     */
    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        if (!$this->apiKey || !file_exists($filename)) {
            return $result;
        }

        $mimeType = mime_content_type($filename);
        $fileData = base64_encode(file_get_contents($filename));

        $keys     = array_keys($result);
        $keysJson = json_encode($keys, JSON_UNESCAPED_UNICODE);
        $prompt   = "Analiza el documento adjunto y extrae los siguientes campos. "
                  . "Responde ÚNICAMENTE con un objeto JSON válido con exactamente estas claves: $keysJson. "
                  . "Si un campo no se encuentra en el documento, usa string vacío \"\". "
                  . "No incluyas explicaciones, bloques de código ni texto adicional. Solo el JSON.";

        // Claude acepta PDF como 'document' e imágenes como 'image'
        $type      = ($mimeType === 'application/pdf') ? 'document' : 'image';
        $fileBlock = [
            'type'   => $type,
            'source' => ['type' => 'base64', 'media_type' => $mimeType, 'data' => $fileData]
        ];

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 4096,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    $fileBlock,
                    ['type' => 'text', 'text' => $prompt]
                ]
            ]]
        ];

        // Usamos curl propio con timeout mayor para archivos pesados
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
                "x-api-key: " . $this->apiKey,
                "anthropic-version: 2023-06-01"
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 120
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return $result;
        }

        $responseJson = json_decode($response, true);
        $text         = $responseJson['content'][0]['text'] ?? '';

        if ($text) {
            $parsed = $this->parseJsonResponse($text);
            foreach ($result as $key => $_) {
                if (isset($parsed[$key])) {
                    $result[$key] = $parsed[$key];
                }
            }
        }

        return $result;
    }

    private function parseJsonResponse(string $text): array
    {
        $decoded = json_decode($text, true);
        if ($decoded !== null) return $decoded;

        // Intenta extraer el JSON si viene envuelto en texto
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if ($decoded !== null) return $decoded;
        }

        return [];
    }
}
