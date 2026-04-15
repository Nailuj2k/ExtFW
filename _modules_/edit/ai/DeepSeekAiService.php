<?php

namespace AiServices;

//use AiServices\AiServiceInterface;



class DeepSeekAiService implements AiServiceInterface {

    private $endPoint = 'https://api.deepseek.com/chat/completions';

    function askQuestion($query) {
        $api_key = DEEPSEEK_API_KEY;
        $url = 'https://api.deepseek.com/chat/completions';

        $history_file = SCRIPT_DIR_MODULE.'/history_deepseek_' . session_id() . '.txt';
        $history = file_exists($history_file) ? file_get_contents($history_file) : '';

        $data = array(
            'model' => 'deepseek-chat',
            'messages' => array(
                array('role' => 'system', 'content' => $history ?: 'You are a helpful coding assistant.'),
                array('role' => 'user', 'content' => $query)
            )
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        // curl_close($ch); // Not needed in PHP 8.0+ (auto-closed)

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

    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        return $result; // DeepSeek-chat no soporta lectura de archivos
    }

}

