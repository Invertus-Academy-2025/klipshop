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
    public function __construct(
        private BestSellingProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
    ){}

    #[Route('/products', name: 'products')]
    public function products(): Response
    {
        $products = $this->productRepository->findAll();

        $latestProducts = [];

        foreach ($products as $product) {
            $productId = $product->getProductId();
            $syncedAt = $product->getSyncedAt();

            if (!isset($latestProducts[$productId]) ||
            $syncedAt > $latestProducts[$productId]->getSyncedAt()) {
                $latestProducts[$productId] = $product;
            }
        }

        $products = array_values($latestProducts);

        return $this->render('products/index.html.twig', [
            'products' => $products
        ]);
    }
    #[Route('/product/{id}/delete', name: 'product_delete')]
    public function productDelete($id): Response
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return $this->redirectToRoute('products');
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
        $products_data = json_decode($request->getContent(), true);

        if(isset($products_data['productId'])) {
            $products_data = [$products_data];
        }

        foreach ($products_data as $product_data) {


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

        }
        $this->entityManager->flush();

        return new JsonResponse('Success', Response::HTTP_CREATED);
    }

}
