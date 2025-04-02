<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIService
{
    private $httpClient;
    private ParameterBagInterface $parameterBag;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $parameterBag)
    {
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
    }

    public function analyzeProduct(array $productData): array
    {
        if (empty($productData)) {
            return ['success' => false, 'error' => 'Product data is empty or invalid'];
        }

        $prompt = 'Analyze this product and provide insights: ' . json_encode($productData);

        $apiKey = $this->parameterBag->get('OPENAI_API_KEY');
        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a product analysis assistant.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 500,
                ],
            ]);

            $data = $response->toArray();

            return [
                'success' => true,
                'analysis' => $data['choices'][0]['message']['content'] ?? 'No analysis available',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
}
