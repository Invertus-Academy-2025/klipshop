<?php

namespace App\Controller;

use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductAnalysisController extends AbstractController
{
    private $aiService;
    private $shopUrl;
    private $openaiApiKey;

    public function __construct(
        AIService $aiService,
        string $shopUrl,
        string $openaiApiKey
    ) {
        $this->aiService = $aiService;
        $this->shopUrl = $shopUrl;
        $this->openaiApiKey = $openaiApiKey;
    }

    #[Route('/api/products/analyze', name: 'analyze_product', methods: ['GET'])]
    public function analyzeProduct(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['product']) || !is_array($data['product'])) {
            return $this->json(['error' => 'Invalid product data provided'], 400);
        }

        $analysis = $this->aiService->analyzeProduct($data['product']);

        return $this->json($analysis);
    }
}

