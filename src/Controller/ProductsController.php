<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BestSellingProductRepository;
use Symfony\Component\Serializer\SerializerInterface;

class ProductsController extends AbstractController
{
    private $productRepository;

    public function __construct(BestSellingProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/products', name: 'products')]
    public function products(): Response
    {
        $products = $this->productRepository->findAll();
        return $this->render('products/index.html.twig', [
            'products' => $products
        ]);

    }

    #[Route('/api/products', name: 'api_products')]
    public function apiProducts(SerializerInterface $serializer): Response
    {
        $products = $this->productRepository->findAll();
        $jsonContent = $serializer->serialize($products, 'json');
        return JsonResponse::fromJsonString($jsonContent);
    }
}
