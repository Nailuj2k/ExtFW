<?php

namespace AiServices;

//use AiServices\AiServiceInterface;

class DummyAiService implements AiServiceInterface {

    //private $endPoint = 'https://api.deepseek.com/v1/converse';
    private $apiKey;

    public function askQuestion($query) {
        // Dummy AI service for testing without consuming API tokens
        $queryLength = strlen($query);
        $wordCount = str_word_count($query);

        // If it looks like a chat message (contains "Context:" or questions)
        if (strpos($query, 'Question:') !== false) {
            // Extract the question part
            preg_match('/Question:\s*(.+?)(\n|$)/s', $query, $matches);
            $question = $matches[1] ?? $query;
            return "[DUMMY] Echo: " . trim($question) . "\n(Query: {$wordCount} words, {$queryLength} chars)";
        }

        // If it looks like a code completion request
        if (strpos($query, 'Complete the following') !== false) {
            return "// [DUMMY] Code completion placeholder\n// Query had {$wordCount} words\nfunction exampleCompletion() {\n    return 'dummy';\n}";
        }

        // Generic echo response
        $preview = substr($query, 0, 100);
        if (strlen($query) > 100) $preview .= '...';

        return "[DUMMY AI]\nReceived: {$preview}\nStats: {$wordCount} words, {$queryLength} characters";
    }

    public function parseFile(string $filename, array $result = ['text' => '']): array
    {
        return $result; // Dummy: devuelve el array sin rellenar
    }

}