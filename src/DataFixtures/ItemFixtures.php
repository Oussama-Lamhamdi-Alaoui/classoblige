<?php

namespace App\DataFixtures;

use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        for ($i = 0; $i < 10; $i++) { 
            $item = new Item;
            $item->setItemName('Item N'.$i)
                ->setItemPrice($i * 100)
                ->setItemStock($i * 2)
                ->setItemImage('http://placehold.it/380x380')
                ->setItemSize('XXL')
                ->setItemDateAdded(new \DateTime())
                ->setItemType('polo')
                ->setItemOnSale(FALSE)
            ;
            $manager->persist($item);
        }

        $manager->flush();

    }
}
