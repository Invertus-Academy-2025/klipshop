<?php

namespace App\Tests\Controller;

use App\Entity\BestSellingProduct;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProductsControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Clear the database
        $this->entityManager->createQuery('DELETE FROM App\Entity\BestSellingProduct')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // Create a test user
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function loginUser(): void
    {
        $this->client->request(
            'POST',
            '/login',
            [
                '_username' => 'admin@example.com',
                '_password' => 'admin',
                '_csrf_token' => 'dummy',
            ]
        );

        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testProductsList(): void
    {
        $this->loginUser();

        // Create a test product
        $product = new BestSellingProduct();
        $product->setProductId(1);
        $product->setName('Test Product');
        $product->setTotalSold(100);
        $product->setSyncedAt(new \DateTime());

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        // Test the products list page
        $this->client->request('GET', '/products');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Test Product');
    }

    public function testProductDelete(): void
    {
        $this->loginUser();

        // Create a test product
        $product = new BestSellingProduct();
        $product->setProductId(2);
        $product->setName('Product to Delete');
        $product->setTotalSold(50);
        $product->setSyncedAt(new \DateTime());

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        $productId = $product->getId();

        // Test product deletion
        $this->client->request('GET', "/product/{$productId}/delete");
        $this->assertResponseRedirects('/products');

        // Clear the entity manager to ensure we get fresh data
        $this->entityManager->clear();

        // Verify the product was deleted
        $deletedProduct = $this->entityManager->getRepository(BestSellingProduct::class)->find($productId);
        $this->assertNull($deletedProduct);
    }

    public function testApiProducts(): void
    {
        $this->loginUser();

        // Create a test product
        $product = new BestSellingProduct();
        $product->setProductId(3);
        $product->setName('API Test Product');
        $product->setTotalSold(75);
        $product->setSyncedAt(new \DateTime());

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        // Test the API endpoint
        $this->client->request('GET', '/api/products');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function testApiProductsSave(): void
    {
        $this->loginUser();

        // Test saving a single product
        $productData = [
            'productId' => 4,
            'name' => 'New API Product',
            'totalSold' => 200
        ];

        $this->client->request(
            'POST',
            '/api/products/save',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        // Verify the product was saved
        $savedProduct = $this->entityManager->getRepository(BestSellingProduct::class)
            ->findOneBy(['productId' => 4]);
        $this->assertNotNull($savedProduct);
        $this->assertEquals('New API Product', $savedProduct->getName());
    }

    public function testApiProductsSaveMultiple(): void
    {
        $this->loginUser();

        // Clear any existing products
        $this->entityManager->createQuery('DELETE FROM App\Entity\BestSellingProduct')->execute();

        // Test saving multiple products
        $productsData = [
            [
                'productId' => 5,
                'name' => 'Product 1',
                'totalSold' => 150
            ],
            [
                'productId' => 6,
                'name' => 'Product 2',
                'totalSold' => 250
            ]
        ];

        $this->client->request(
            'POST',
            '/api/products/save',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productsData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        // Verify both products were saved
        $savedProducts = $this->entityManager->getRepository(BestSellingProduct::class)
            ->findBy(['productId' => [5, 6]]);
        $this->assertCount(2, $savedProducts);
    }

    public function testApiProductsSaveValidation(): void
    {
        $this->loginUser();

        // Test validation with invalid data
        $invalidProductData = [
            'productId' => 0, // Zero productId should fail validation
            'name' => '', // Empty name should fail validation
            'totalSold' => -1 // Negative totalSold should fail validation
        ];

        $this->client->request(
            'POST',
            '/api/products/save',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidProductData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clear the database
        if ($this->entityManager) {
            $this->entityManager->createQuery('DELETE FROM App\Entity\BestSellingProduct')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
} 