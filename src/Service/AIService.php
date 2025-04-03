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

        $json = json_encode($productData, JSON_PRETTY_PRINT);

        $prompt = "You are an expert product analyst. Here is a array of product data:

        {$json}

        Each product has:
        - productId
        - name
        - synced_at (timestamp of last update)
        - totalSold (number of units sold)

        Task:
        1. Find products that have the same product_id but different synced_at values (i.e., restocked at different times).
        2. Analyze how often the product gets restocked (based on synced_at).
        3. Compare total_sold values for each restocking instance.
        4. Based on this data, suggest which instance of the product is performing best and whether it should be restocked again.

        ❗Important:
         - Analyze the actual data.
        - Do not return example code.
        - Do not invent data.
        - Respond with a valid **JSON array** of real recommendations based on this data, like:

        Do not return example code. Analyze the data now and return an array with actual recommendations based on the data provided.
        Return only a valid JSON array of recommendations, with this format:
[
    [
        'productId': 123,
        'name' => 'Mug The adventure begins',
        'recommended_synced_at': '2024-03-01T10:00:00Z',
        'reason': 'Best performance'
        ],
        [
        'productId': 456,
        'name' => 'Hummingbird printed t-shirt',
        'recommended_synced_at' => '2024-02-20T14:30:00Z',
        'reason': 'Consistent seller'
       ],
       ...
    ]";

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
