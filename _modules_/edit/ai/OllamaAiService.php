<?php

namespace AIServices;

/**
 * Ollama AI Service (Local o Cloud API)
 *
 * MODO CLOUD (con API key):
 * 1. Obtén tu API key desde: https://ollama.com/settings/keys
 * 2. Configura en tu config:
 *    define('OLLAMA_API_KEY', 'tu-api-key');
 *    define('OLLAMA_MODEL', 'gpt-oss:20b-cloud'); // o gpt-oss:120b-cloud, deepseek-v3.1:671b-cloud, qwen3-coder:480b-cloud
 *
 * MODO LOCAL (sin API key):
 * 1. Instala Ollama desde: https://ollama.ai/
 * 2. Ejecuta: ollama run llama3.2
 * 3. Ollama corre en http://localhost:11434 por defecto
 */
class OllamaAiService implements AiServiceInterface
{
    private $apiUrl;
    private $apiKey;
    private $model;
    private $historyFile;
    private $isCloud = false;

    public function __construct()
    {
        // Verificar si hay API key para usar modo cloud
        $this->apiKey = defined('OLLAMA_API_KEY') ? OLLAMA_API_KEY : null;

        if ($this->apiKey) {
            // Modo Cloud: usar ollama.com
            $this->apiUrl = 'https://ollama.com/api/chat';
            $this->isCloud = true;
        } else {
            // Modo Local: usar localhost
            $host = defined('OLLAMA_HOST') ? OLLAMA_HOST : 'http://localhost:11434';
            $this->apiUrl = rtrim($host, '/') . '/api/chat';
        }

        // Modelo por defecto
        $this->model = defined('OLLAMA_MODEL') ? OLLAMA_MODEL : 'gpt-oss:20b-cloud';

        $this->historyFile = SCRIPT_DIR_MODULE . '/history.ollama.' . session_id() . '.json';
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

        // Configurar datos para la API de Ollama
        $payload = [
            'model' => $this->model,
            'messages' => $history,
            'stream' => false
        ];

        // Realizar la solicitud a Ollama
        $response = $this->sendRequest($payload);

        // Procesar la respuesta
        if ($response && isset($response['message']['content'])) {
            $result = $response['message']['content'];

            // Extraer solo el código del mensaje si viene en bloques markdown
            $result = $this->extractCode($result);

            // Guardar la respuesta en el historial
            $history[] = ["role" => "assistant", "content" => $result];
            $this->saveHistory($history);

            return $result;
        }

        // Manejar errores de la API
        if (isset($response['error'])) {
            return "Error Ollama API: " . $response['error'];
        }

        $modeInfo = $this->isCloud ? "Cloud (ollama.com)" : "Local ({$this->apiUrl})";
        return "Error: No se recibió una respuesta válida de Ollama. Modo: {$modeInfo}";
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
        $headers = [
            "Content-Type: application/json"
        ];

        // Añadir Authorization header si es modo cloud
        if ($this->isCloud && $this->apiKey) {
            $headers[] = "Authorization: Bearer " . $this->apiKey;
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = 'Error en cURL: ' . curl_error($ch);
            curl_close($ch);
            return ["error" => $error];
        }

        curl_close($ch);

        // Verificar si la respuesta es válida
        if ($httpCode !== 200) {
            $errorResponse = json_decode($response, true);
            $errorMsg = $errorResponse['error'] ?? $response;
            return ["error" => "HTTP $httpCode: " . $errorMsg];
        }

        return json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Extrae el código contenido entre bloques markdown (```)
     * Si no se encuentra ningún bloque, devuelve el texto completo.
     */
    private function extractCode($text)
    {
        if (preg_match('/```(?:[a-zA-Z0-9]+)?\s*(.*?)\s*```/s', $text, $matches)) {
            return trim($matches[1]);
        }
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
     * Cambiar el modelo de Ollama en tiempo de ejecución
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Obtener el modelo actual
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Verificar si está en modo cloud
     */
    public function isCloudMode()
    {
        return $this->isCloud;
    }

    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        return $result; // Ollama no soporta lectura de archivos en este servicio
    }
}
