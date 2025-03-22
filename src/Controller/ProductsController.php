<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BestSellingProductRepository;

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
}
