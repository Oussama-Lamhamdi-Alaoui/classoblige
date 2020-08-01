<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{
    public function menPolos(ItemRepository $repo)
    {
        $poloItemList = $repo->findByTypeInStock('polo');

        return $this->render('item/men/polos.html.twig', [
            'poloItemList' => $poloItemList
        ]);
    }
}
