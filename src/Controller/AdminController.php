<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function dashboard()
    {
        return $this->render('admin/dashboard.html.twig');
    }
}
