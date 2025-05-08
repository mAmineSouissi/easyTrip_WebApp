<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger, string $apiKey)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->apiKey = trim($apiKey); // Ensure no whitespace in API key
    }

    public function generateResponse(string $userMessage): string
    {
        try {
            if (empty($this->apiKey)) {
                throw new \Exception('Missing API key. Please set GEMINI_API_KEY in your .env file.');
            }

            // Format request exactly like the curl example
            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $userMessage
                            ]
                        ]
                    ]
                ]
            ];

            $url = $this->baseUrl . '?key=' . urlencode($this->apiKey);

            $this->logger->info('Sending request to Gemini API', [
                'url' => preg_replace('/key=.*/', 'key=HIDDEN', $url),
                'requestData' => $requestData
            ]);

            $response = $this->client->request('POST', $url, [
                'json' => $requestData,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent();
            
            $this->logger->info('Received response from Gemini API', [
                'statusCode' => $statusCode
            ]);

            if ($statusCode !== 200) {
                throw new \Exception("API returned status code: $statusCode");
            }

            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            // Extract text from the response
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $this->logger->error('Invalid response structure', ['data' => $data]);
                throw new \Exception('Invalid response structure from Gemini API');
            }

            return $data['candidates'][0]['content']['parts'][0]['text'];

        } catch (\Exception $e) {
            $this->logger->error('Error in GeminiService', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (str_contains($e->getMessage(), 'API key')) {
                return 'Configuration error: Please make sure your Gemini API key is properly set in the .env file.';
            }
            
            return 'Désolé, je rencontre des difficultés techniques. Veuillez réessayer plus tard.';
        }
    }
} 