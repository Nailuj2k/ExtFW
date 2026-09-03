<?php

namespace AiServices;

//use AiServices\AiServiceInterface;

class LinuxLocalAiService implements AiServiceInterface {
    private $endPoint = 'http://localhost:8080/api/text';
    private $apiKey;

    public function askQuestion($question, $context = []) {
        // Send request to localhost API
        $requestBody = ['prompt' => $question];
        if (!empty($context)) {
            $requestBody['context'] = json_encode($context);
        }
        $ch = curl_init($this->endPoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestBody));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true)['answer'];
    }

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        return $result; // LinuxLocal no soporta lectura de archivos
    }
}