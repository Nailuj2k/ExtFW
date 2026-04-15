<?php

namespace AiServices;

class KimiAiService implements AiServiceInterface {

    // Moonshot AI (Kimi) - compatible con OpenAI format
    private $endPoint = 'https://api.moonshot.cn/v1/chat/completions';

    function askQuestion($query) {
        $api_key = KIMI_API_KEY;
        $url = $this->endPoint;

        $data = array(
            'model' => 'moonshot-v1-8k',
            'messages' => array(
                array('role' => 'system', 'content' => 'You are a helpful coding assistant.'),
                array('role' => 'user', 'content' => $query)
            ),
            'temperature' => 0.3
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

        if ($response === false) {
            return "Error: cURL failed - " . $curlError;
        }

        $response_json = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $response_json['error']['message']
                     ?? $response_json['error']
                     ?? $response_json['message']
                     ?? 'Unknown error';
            return "Error ($httpCode): " . $errorMsg;
        }

        if (isset($response_json['choices'][0]['message']['content'])) {
            return $response_json['choices'][0]['message']['content'];
        }

        return "Error: Unexpected response format - " . substr($response, 0, 200);
    }

    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        return $result; // Kimi Files API no implementada aún
    }

}