<?php

namespace App\Controller;

use App\Entity\BestSellingProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BestSellingProductRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductsController extends AbstractController
{
    private $productRepository;
    private $entityManager;

    public function __construct(BestSellingProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
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
    public function apiProductsSave(Request $request, ValidatorInterface $validator, SerializerInterface $serializer): Response
    {
        $product_data = json_decode($request->getContent(), true);

        $product = new BestSellingProduct();

        $product->setProductId($product_data['productId']);
        $product->setName($product_data['name']);
        $product->setTotalSold($product_data['totalSold']);
        $product->setSyncedAt(new \DateTime());


        $errors = $validator->validate($product);

        if (count($errors) > 0) {
            $jsonContent = $serializer->serialize($errors, 'json');
            return JsonResponse::fromJsonString($jsonContent, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return new JsonResponse('Success', Response::HTTP_CREATED);
    }

}
