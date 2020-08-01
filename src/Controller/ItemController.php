<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{
    public function menPolos(ItemRepository $repo)
    {
        $poloItemList = $repo->findByTypeInStock('menpolo');

        return $this->render('item/men/polos.html.twig', [
            'poloItemList' => $poloItemList
        ]);
    }

    public function menShirts(ItemRepository $repo)
    {
        $shirtItemList = $repo->findByTypeInStock('menshirt');

        return $this->render('item/men/shirts.html.twig', [
            'shirtItemList' => $shirtItemList
        ]);
    }

    public function menPants(ItemRepository $repo)
    {
        $pantsItemList = $repo->findByTypeInStock('menpants');

        return $this->render('item/men/pants.html.twig', [
            'pantsItemList' => $pantsItemList
        ]);
    }
}
