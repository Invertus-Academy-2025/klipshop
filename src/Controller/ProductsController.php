<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BestSellingProductRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/api/products/save', name: 'api_products_save', methods: ['POST'])]
    public function apiProductsSave(Request $request): Response
    {
        $product_data = json_decode($request->getContent(), true);

        $fields = ['productId', 'name', 'totalSOld'];

        foreach ($fields as $field) {
            if (empty($product_data[$field])) {
                return new JsonResponse(['error' => 'Field "' . $field . '" is required.'], Response::HTTP_BAD_REQUEST);
            }
        }
        $product_data['synced_at'] = (new \DateTime())->format('Y-m-d H:i:s');

        return new JsonResponse('Success', 200);
    }


}
