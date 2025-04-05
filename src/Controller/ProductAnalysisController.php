<?php

namespace App\Controller;

use App\Repository\BestSellingProductRepository;
use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductAnalysisController extends AbstractController
{
    public function __construct(
        private AIService $aiService,
        private BestSellingProductRepository $repository
    ) {}

    #[Route('/api/products/analyze', name: 'analyze_products', methods: ['GET'])]
    public function analyzeProduct(): Response
    {
        $data = $this->repository->findAll();
        $data = array_map(function ($product) {
            return [
                'productId' => $product->getProductId(),
                'name' => $product->getName(),
                'synced_at' => $product->getSyncedAt(),
                'totalSold' => $product->getTotalSold()
            ];
        }, $data);

        $analysis = $this->aiService->analyzeProduct($data);

        if (!$analysis['success']) {
            return $this->render('products/analytics.html.twig', [
                'products' => [],
                'error' => 'AI analysis request failed.'
            ]);
        }

        $rawJson = $analysis['analysis'];
        $clean = preg_replace('/```json|```/', '', $rawJson);
        $products = json_decode(trim($clean), true);

        if (!is_array($products)) {
            return $this->render('products/analytics.html.twig', [
                'products' => [],
                'error' => 'Invalid product data type received from analysis'
            ]);
        }

        return $this->render('products/analytics.html.twig', [
            'products' => $products,
            'error' => null
        ]);
    }
}

