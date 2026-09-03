<?php

namespace AiServices;

class CerebrasAiService implements AiServiceInterface {

    private $endPoint = 'https://api.cerebras.ai/v1/chat/completions';
    private $model    = 'llama3.1-8b';

    function askQuestion($query) {

        $api_key = CEREBRAS_API_KEY;

        if (empty($api_key)) {
            return "Error: Cerebras API key not configured (ai.cerebras.api_key).";
        }

        $data = [
            'model'                => $this->model,
            'messages'             => [
                ['role' => 'system', 'content' => 'You are a helpful coding assistant.'],
                ['role' => 'user',   'content' => $query]
            ],
            'max_completion_tokens' => 1024,
            'temperature'          => 0.2,
            'top_p'                => 1,
            'stream'               => false
        ];

        $ch = curl_init($this->endPoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return "Error: cURL failed - " . $curlError;
        }

        $response_json = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $response_json['error']['message'] ?? $response_json['message'] ?? 'Unknown error';
            return "Error ($httpCode): " . $errorMsg;
        }

        if (isset($response_json['choices'][0]['message']['content'])) {
            return $response_json['choices'][0]['message']['content'];
        }

        return "Error: Unexpected response format - " . substr($response, 0, 200);
    }

    public function parseFile(string $filename, array $result = ['text' => '']): array {
        return $result; // Cerebras no soporta lectura de archivos
    }

}
