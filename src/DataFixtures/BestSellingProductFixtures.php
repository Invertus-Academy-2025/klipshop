<?php

namespace App\DataFixtures;

use App\Entity\BestSellingProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BestSellingProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //Fake Data

        $product1 = new BestSellingProduct();
        $product1->setProductId(1);
        $product1->setName('T-Shirt');
        $product1->setTotalSold(100);
        $product1->setSyncedAt(new \DateTime());
        $manager->persist($product1);

        $product2 = new BestSellingProduct();
        $product2->setProductId(2);
        $product2->setName('Painting');
        $product2->setTotalSold(20);
        $product2->setSyncedAt(new \DateTime());
        $manager->persist($product2);


        $product3 = new BestSellingProduct();
        $product3->setProductId(3);
        $product3->setName('Mug');
        $product3->setTotalSold(80);
        $product3->setSyncedAt(new \DateTime());
        $manager->persist($product3);


        $manager->flush();
    }
}
