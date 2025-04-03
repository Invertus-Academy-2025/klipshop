<?php

namespace App\Controller;

use App\Repository\BestSellingProductRepository;
use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductAnalysisController extends AbstractController
{
    private $aiService;

    public function __construct(
        AIService $aiService,
        BestSellingProductRepository $repository
    ) {
        $this->aiService = $aiService;
        $this->repository = $repository;
    }

    #[Route('/api/products/analyze', name: 'analyze_product', methods: ['GET'])]
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

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid product type provided'], 400);
        }

        $analysis = $this->aiService->analyzeProduct($data);

        if (!$analysis['success']) {
            return new Response('Request failed', 400);
        }

        $products = json_decode($analysis['analysis'], true);

        if (!is_array($products)) {
            return new Response('Invalid product type provided', 400);
        }

        return $this->render('products/analytics.html.twig', [
            'products' => $products
        ]);
    }
}

