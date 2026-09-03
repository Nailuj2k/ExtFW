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

    public $lastError = '';

    public function getLastError()
    {
        return $this->lastError;
    }

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

    /**
     * Lee un archivo (PDF o imagen) y extrae los campos indicados usando un modelo de vision.
     *
     * A diferencia de Claude/Gemini, Ollama NO acepta PDF: solo imagenes en el array
     * 'images' (base64). Por eso, si el archivo es PDF, se convierte cada pagina a JPG
     * con ImageMagick (una imagen por pagina, sin -append) y se envian todas juntas.
     *
     * Requiere un modelo de vision. Se resuelve por OLLAMA_VISION_MODEL (default
     * 'llama3.2-vision'), NO por OLLAMA_MODEL (que puede ser un modelo solo de texto).
     *
     * @param string $filename  Ruta absoluta al archivo
     * @param array  $result    Array asociativo cuyas keys son los campos a extraer
     * @return array            El mismo array con los valores rellenados
     */
    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        $this->lastError = '';

        if (!file_exists($filename)) {
            $this->lastError = 'Archivo no encontrado: ' . $filename;
            return $result;
        }

        $mimeType = mime_content_type($filename);
        $maxPages = 25;
        $b64      = [];

        if ($mimeType === 'application/pdf') {
            // Preferencia: extension Imagick (sin proceso externo ni ficheros temporales).
            // Fallback: binario 'convert' via shell_exec.
            if (extension_loaded('imagick')) {
                try {
                    $im = new \Imagick();
                    $im->setResolution(150, 150); // debe ir ANTES de readImage
                    $im->readImage($filename);
                    foreach ($im as $page) {
                        $page->setImageBackgroundColor('white');
                        $flat = $page->flattenImages(); // fusiona alfa sobre blanco
                        $flat->setImageFormat('jpg');
                        $flat->setImageCompressionQuality(85);
                        $b64[] = base64_encode($flat->getImageBlob());
                        $flat->clear();
                        if (count($b64) >= $maxPages) break;
                    }
                    $im->clear();
                } catch (\Exception $e) {
                    $this->lastError = 'Imagick fallo al rasterizar el PDF: ' . $e->getMessage();
                    return $result;
                }
                if (empty($b64)) {
                    $this->lastError = 'Imagick no produjo paginas del PDF.';
                    return $result;
                }
            } elseif (function_exists('shell_exec')) {
                $tmpBase = sys_get_temp_dir() . '/ollama_' . uniqid();
                $out     = $tmpBase . '.jpg';
                $cmd     = 'convert -density 150 -quality 85 -alpha remove -background white '
                         . \escapeshellarg($filename) . ' ' . \escapeshellarg($out) . ' 2>&1';
                $convOut = \shell_exec($cmd);

                // convert escribe base.jpg si es 1 pagina, o base-0.jpg, base-1.jpg... si son varias
                $pages = \glob($tmpBase . '*.jpg');
                if (empty($pages)) {
                    $this->lastError = 'Fallo al convertir PDF a imagen (ImageMagick). Salida de convert: '
                                     . trim((string)$convOut);
                    return $result;
                }
                natsort($pages);
                $pages = array_slice(array_values($pages), 0, $maxPages);
                foreach ($pages as $p) {
                    $b64[] = base64_encode(file_get_contents($p));
                    @unlink($p);
                }
            } else {
                $this->lastError = 'Este servidor no puede convertir PDF a imagen: falta la extension Imagick '
                                 . 'y shell_exec esta deshabilitado. Para PDFs usa Claude/Gemini/OpenAI, '
                                 . 'o sube el documento como imagen (jpg/png) en vez de PDF.';
                return $result;
            }
        } else {
            $b64[] = base64_encode(file_get_contents($filename));
        }

        $keys     = array_keys($result);
        $keysJson = json_encode($keys, JSON_UNESCAPED_UNICODE);
        $prompt   = "Analiza el documento adjunto (una o varias imagenes son paginas del mismo documento) "
                  . "y extrae los siguientes campos. "
                  . "Responde UNICAMENTE con un objeto JSON valido con exactamente estas claves: $keysJson. "
                  . "Si un campo no se encuentra en el documento, usa string vacio \"\". "
                  . "En el campo text del JSON pon una transcripcion e interpretacion completa del documento en castellano, "
                  . "identificando datos como DNIs, numeros de certificado, NHC, nombres, direcciones, medicos, "
                  . "prioridades, edades, tipos, actas, solicitudes y fechas. "
                  . "No incluyas explicaciones, bloques de codigo ni texto adicional. Solo el JSON.";

        $visionModel = defined('OLLAMA_VISION_MODEL') ? OLLAMA_VISION_MODEL : 'llama3.2-vision';

        $payload = [
            'model'    => $visionModel,
            'messages' => [[
                'role'    => 'user',
                'content' => $prompt,
                'images'  => $b64
            ]],
            'stream'   => false,
            'format'   => 'json',
            'options'  => ['temperature' => 0.1]
        ];

        $response = $this->sendRequest($payload);

        if (!$response || !isset($response['message']['content'])) {
            if (isset($response['error'])) {
                $this->lastError = 'Ollama (' . $this->apiUrl . ', modelo ' . $visionModel . '): ' . $response['error'];
            } else {
                $this->lastError = 'Respuesta inesperada de Ollama (' . $this->apiUrl . ', modelo '
                                 . $visionModel . '): ' . substr((string)json_encode($response), 0, 300);
            }
            return $result;
        }

        $text   = $response['message']['content'];
        $parsed = json_decode($text, true);
        if ($parsed === null && preg_match('/\{.*\}/s', $text, $matches)) {
            $parsed = json_decode($matches[0], true);
        }

        if (is_array($parsed)) {
            foreach ($result as $key => $_) {
                if (isset($parsed[$key])) {
                    $result[$key] = $parsed[$key];
                }
            }
        }

        return $result;
    }
}
